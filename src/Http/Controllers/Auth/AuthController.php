<?php

namespace Mariojgt\Candle\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mariojgt\Candle\Models\MagicLink;

class AuthController extends Controller
{
    /**
     * Show login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('candle::auth.login');
    }

    /**
     * Handle magic link login request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We couldn\'t find a user with that email address.',
            ]);
        }

        // Generate a random token
        $token = Str::random(64);

        // Set expiration time (e.g., 15 minutes from now)
        $expiresAt = Carbon::now()->addMinutes(config('candle.magic_links.expires_in', 15));

        // Create magic link
        $magicLink = new MagicLink([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        $magicLink->save();

        // Build the magic link URL
        $url = url(route('candle.auth.magic-link', ['token' => $token]));

        // Send the email
        Mail::send('candle::emails.magic-link', ['url' => $url, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Your Magic Login Link');
        });

        return back()->with('status', 'We\'ve sent a magic link to your email address!');
    }

    /**
     * Process magic link login.
     *
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processMagicLink($token)
    {
        $magicLink = MagicLink::where('token', $token)->first();

        if (!$magicLink || !$magicLink->isValid()) {
            return redirect()->route('candle.auth.login')->withErrors([
                'token' => 'This magic link is invalid or has expired.',
            ]);
        }

        // Log the user in
        Auth::login($magicLink->user);

        // Delete the used magic link
        $magicLink->delete();

        return redirect()->intended(route('candle.dashboard'));
    }

    /**
     * Log in with traditional credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('candle.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('candle::auth.register');
    }

    /**
     * Handle registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userModel = config('auth.providers.users.model');

        $user = $userModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('candle.dashboard');
    }

    /**
     * Log the user out.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('candle.auth.login');
    }
}
