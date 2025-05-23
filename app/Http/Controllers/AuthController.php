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
        // Remplacer auth:api par auth (utilise le garde web par défaut)
        $this->middleware('auth', ['except' => ['login', 'showLoginForm', 'showForgotPasswordForm', 'sendResetCode', 'showResetForm', 'resetPassword']]);
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
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
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

        if (!$token = auth()->attempt($validator->validated())) {
            return redirect()->back()
                ->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.'])
                ->withInput();
        }

        // Store token in session
        session(['jwt_token' => $token]);

        // Redirect based on user role
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('pharmacist.dashboard');
        }
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

        // Générer et envoyer le code
        $code = PasswordResetCode::generateCode($email);

        try {
            Mail::to($email)->send(new PasswordResetCodeMail($code, $user->name));
            
            return redirect()->route('password.reset.form', ['email' => $email])
                ->with('success', 'Un code de vérification a été envoyé à votre adresse email.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'])
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
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        auth()->logout();
        session()->forget('jwt_token');
        return redirect()->route('login');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth()->refresh();
        session(['jwt_token' => $token]);
        return response()->json(['token' => $token]);
    }
}