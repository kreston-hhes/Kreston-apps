<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Session;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 1. Tambahkan middleware session authentication
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        // 2. Atur redirect untuk Guest (User yang tidak terautentikasi/ditendang)
        $middleware->redirectGuestsTo(function () {
            // Kita cek jika user sebelumnya terautentikasi tapi tiba-tiba tidak (session expired/double login)
            // Kita kirimkan flash notification
            Session::flash('notification', [
                'variant' => 'warning',
                'title'   => 'Session Expired',
                'message' => 'Anda telah keluar karena akun ini login di perangkat lain.'
            ]);

            return route('login');
        });

        // 3. Atur redirect untuk user yang sudah login tapi mencoba akses halaman guest
        $middleware->redirectUsersTo('/dashboard');

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();