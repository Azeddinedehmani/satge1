<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
    $this->middleware('auth', ['except' => ['login', 'showLoginForm']]);
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