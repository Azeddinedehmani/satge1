<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\PasswordResetCode;
use App\Mail\PasswordResetCode as PasswordResetCodeMail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'showLoginForm', 'showForgotPasswordForm', 'sendResetCode', 'showResetForm', 'resetPassword']]);
        $this->middleware('guest', ['only' => ['showLoginForm', 'login', 'showForgotPasswordForm', 'sendResetCode', 'showResetForm', 'resetPassword']]);
    }

    /**
     * Show login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('pharmacist.dashboard'));
            }
        }
// In AuthController::login method, after successful authentication, add:
if (Auth::attempt($credentials, $remember)) {
    $request->session()->regenerate();
    
    $user = Auth::user();
    
    // Update last login info
    $user->update([
        'last_login_at' => now(),
        'last_login_ip' => $request->ip(),
    ]);
    
    // Log login activity
    ActivityLog::logActivity(
        'login',
        'Connexion réussie',
        null,
        null,
        ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
    );
    
    Log::info('User logged in successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'ip' => $request->ip()
    ]);

    // Redirect based on user role
    if ($user->isAdmin()) {
        return redirect()->intended(route('admin.dashboard'));
    } else {
        return redirect()->intended(route('pharmacist.dashboard'));
    }
}
        return redirect()->back()
            ->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.'])
            ->withInput();
    }

    /**
     * Show forgot password form
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send reset code to user's email
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->email;
        
        // Rate limiting: max 3 attempts per hour per email
        $key = 'password-reset-' . $email;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return redirect()->back()
                ->withErrors(['email' => "Trop de tentatives. Réessayez dans " . ceil($seconds/60) . " minutes."])
                ->withInput();
        }

        RateLimiter::hit($key, 3600); // 1 hour window

        $user = User::where('email', $email)->first();

        // Log the attempt
        Log::info('Password reset code generation attempt', [
            'email' => $email,
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        try {
            // Générer le code
            $code = PasswordResetCode::generateCode($email);
            
            Log::info('Password reset code generated', [
                'email' => $email,
                'code' => $code // Log the actual code for debugging
            ]);

            // Try to send email with timeout handling
            $mailSent = false;
            $errorMessage = '';
            
            try {
                // Set a shorter timeout for email sending
                ini_set('max_execution_time', 30);
                
                Mail::to($email)->send(new PasswordResetCodeMail($code, $user->name));
                $mailSent = true;
                
                Log::info('Password reset code email sent successfully', [
                    'email' => $email,
                    'user_id' => $user->id
                ]);
                
            } catch (\Exception $mailException) {
                $errorMessage = $mailException->getMessage();
                Log::error('Failed to send password reset code email', [
                    'email' => $email,
                    'error' => $errorMessage,
                    'trace' => $mailException->getTraceAsString()
                ]);
            }
            
            // Reset execution time limit
            ini_set('max_execution_time', 60);
            
            if ($mailSent) {
                return redirect()->route('password.reset.form', ['email' => $email])
                    ->with('success', 'Un code de vérification a été envoyé à votre adresse email.');
            } else {
                // Email failed but code was generated - show the code for development
                if (config('app.debug')) {
                    return redirect()->route('password.reset.form', ['email' => $email])
                        ->with('success', "Code de vérification généré: <strong>{$code}</strong> (Email non envoyé: {$errorMessage})");
                } else {
                    return redirect()->back()
                        ->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Le code a été généré mais l\'envoi a échoué.'])
                        ->withInput();
                }
            }
                
        } catch (\Exception $e) {
            Log::error('Failed to generate password reset code', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['email' => 'Erreur lors de la génération du code. Veuillez réessayer plus tard.'])
                ->withInput();
        }
    }

    /**
     * Show reset password form
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Email requis pour la réinitialisation.']);
        }

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Reset password with verification code
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Check for too many attempts
        if (PasswordResetCode::hasTooManyAttempts($request->email)) {
            Log::warning('Password reset blocked due to too many attempts', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            
            return redirect()->back()
                ->withErrors(['code' => 'Trop de tentatives incorrectes. Demandez un nouveau code.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Vérifier le code
        if (!PasswordResetCode::verifyCode($request->email, $request->code)) {
            Log::warning('Invalid password reset code attempted', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            
            return redirect()->back()
                ->withErrors(['code' => 'Code de vérification invalide ou expiré.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log successful password reset
        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        // Nettoyer les codes expirés
        PasswordResetCode::cleanExpired();

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Log the user out
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}