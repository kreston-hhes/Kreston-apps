<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
       return view('pages.auth.signin', ['title' => 'Sign In']);
    }

public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // 1. Lakukan Attempt Login
    if (Auth::attempt($credentials, $request->has('remember'))) {
        
        // 2. Cegah Multiple Login
        // Penting: Masukkan password asli dari request
        Auth::logoutOtherDevices($request->password);

        // 3. Regenerate session ID
        $request->session()->regenerate();

        if (!Auth::user()->active) {
            Auth::logout();
            AlertService::notify('warning', 'Access Denied', 'Account is deactivated.');
            return redirect()->route('login');
        }

        AlertService::notify('success', 'Login Successful', 'Welcome back!');
        return redirect()->route('dashboard');
    }

    AlertService::notify('error', 'Login Failed', 'Email or password is incorrect.');
    return back();
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
// Langsung tembak ke rute 'login', jangan ke '/' yang terproteksi auth
    return redirect()->route('login')->with('notification', [
        'variant' => 'success',
        'title'   => 'Logout Berhasil',
        'message' => 'Sampai jumpa kembali!'
    ]);
    }

    public function showForgotPassword()
    {
        return view('pages.auth.forgot-password', ['title' => 'Forgot Password']);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            AlertService::notify(
                'success',
                'Email Terkirim',
                'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.'
            );

            return back();
        }

        if ($status === Password::INVALID_USER) {
            AlertService::notify(
                'error',
                'Email Tidak Terdaftar',
                'Alamat email ini tidak ditemukan. Pastikan Anda mendaftarkan akun terlebih dahulu.'
            );

            return back();
        }

        AlertService::notify(
            'warning',
            'Gagal Mengirim Email',
            'Permintaan reset password belum berhasil. Silakan coba lagi nanti.'
        );

        return back();
    }

    public function showResetPassword(Request $request)
    {
        return view('pages.auth.reset-password', [
            'title' => 'Reset Password',
            'request' => $request
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            AlertService::notify(
                'success',
                'Password Berhasil Diubah',
                'Password Anda sudah diperbarui. Silakan masuk kembali dengan password baru.'
            );

            return redirect()->route('login');
        }

        if ($status === Password::INVALID_TOKEN) {
            AlertService::notify(
                'error',
                'Token Tidak Valid',
                'Tautan reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.'
            );

            return back();
        }

        if ($status === Password::INVALID_USER) {
            AlertService::notify(
                'error',
                'Email Tidak Terdaftar',
                'Alamat email ini tidak ditemukan. Pastikan Anda menggunakan email yang benar.'
            );

            return back();
        }

        AlertService::notify(
            'warning',
            'Reset Gagal',
            'Reset password gagal. Silakan periksa kembali data yang dimasukkan dan coba lagi.'
        );

        return back();
    }
}
