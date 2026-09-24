<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTicketController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\AssetTypeController;

// PUBLIC TICKET DOMAIN

Route::domain('ticket.' . env('APP_SHORT_URL', 'kreston.id'))->group(function () {

Route::get('/', [PublicTicketController::class, 'index'])
        ->name('public-ticket.form');
//submit ticket support
    Route::post('/', [PublicTicketController::class, 'submitTicket'])->name('public-ticket-support.submit');
//Find Status ticket
Route::get('/{ticketNumber}', [PublicTicketController::class, 'checkStatus'])
    ->name('public-ticket.status');



});

//INTERNAL APPS DOMAIN

Route::domain('apps.' . env('APP_SHORT_URL', 'kreston.id'))->group(function () {
// 1. Rute TAMU (Bisa diakses tanpa login)
Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/signin', [AuthController::class, 'login']);


    // Forgot Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// 2. Rute TERPROTEKSI
Route::middleware('auth')->group(function () {
Route::get('/test-form', [TestController::class, 'index']);
    Route::post('/test-form', [TestController::class, 'store']);
    /*
    |--------------------------------------------------------------------------
    | Umum
    |--------------------------------------------------------------------------
    | Semua pengguna yang sudah login bisa mengakses rute ini, tanpa memandang divisi
    */
    // Gunakan rute dashboard yang jelas
    Route::get('/dashboard', function () {
        return view('pages.dashboard.dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');

    // profile pages
Route::get('/profile', [DashboardController::class, 'showProfile'])->name('profile.show');
Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');

//change password
Route::put('/password-update', [DashboardController::class, 'updatePassword'])->name('password.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | IT MENU
    |--------------------------------------------------------------------------
    | Hanya divisi IT
    */

    Route::middleware(['division:IT'])->group(function () {

    /*
|--------------------------------------------------------------------------
| Asset Management
|--------------------------------------------------------------------------
|
*/

    // Data Asset Utama
    // Route untuk fitur Data Aset Utama
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    
    Route::delete('/it/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('/assets/categories', [AssetController::class, 'storeCategory'])->name('assets.categories.store');
    Route::patch('/it/assets/{id}/qc', [AssetController::class, 'updateQcStatus'])->name('assets.qc.update');
    // Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

    // Route khusus untuk form Modal Tambah Kategori Baru, tapi masih bingung sih
    Route::post('/it/asset-types', [AssetTypeController::class, 'store'])->name('asset-types.store');

    // Surat Penyerahan
    Route::get('/assets/surat-penyerahan', [HandoverController::class, 'index'])->name('handovers.index');
    Route::post('/assets/surat-penyerahan', [HandoverController::class, 'store'])->name('handovers.store');
    Route::get('/assets/surat-penyerahan/pdf', [HandoverController::class, 'printPenyerahan'])->name('handovers.pdf');
    Route::delete('/assets/surat-penyerahan/{assignment}', [HandoverController::class, 'destroy'])->name('handovers.destroy');

    // Surat Pengembalian
    Route::get('/assets/surat-pengembalian', [HandoverController::class, 'returnIndex'])->name('handovers.return.index');
    Route::patch('/assets/surat-pengembalian/{assignment}', [HandoverController::class, 'processReturn'])->name('handovers.return.process');
    Route::get('/assets/surat-pengembalian/pdf', [HandoverController::class, 'printPengembalian'])->name('handovers.return.pdf');


    // Logbook Peminjaman
    Route::get('/assets/logbook', [App\Http\Controllers\LoanController::class, 'index'])->name('loans.index');
    Route::post('/assets/logbook', [App\Http\Controllers\LoanController::class, 'store'])->name('loans.store');
    Route::patch('/assets/logbook/{loan}/selesai', [App\Http\Controllers\LoanController::class, 'finish'])->name('loans.finish');
    

    // Arsip Surat
    Route::get('/assets/arsip-surat', [App\Http\Controllers\ArchiveController::class, 'index'])->name('archives.index');

    // Consumable
    Route::get('/consumables', [App\Http\Controllers\ConsumableController::class, 'index'])->name('consumables.index');
    Route::post('/consumables', [App\Http\Controllers\ConsumableController::class, 'store'])->name('consumables.store');
    Route::patch('/consumables/{consumable}', [App\Http\Controllers\ConsumableController::class, 'update'])->name('consumables.update');

    // Billing Bulanan Printer
    Route::get('/printer-billings', [App\Http\Controllers\PrinterBillingController::class, 'index'])->name('printer-billings.index');
    Route::post('/printer-billings', [App\Http\Controllers\PrinterBillingController::class, 'store'])->name('printer-billings.store');

    // Invoice
    Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    /*
|--------------------------------------------------------------------------
| Ticket Support
|--------------------------------------------------------------------------
|
*/
       //Route to Ticket Support
    Route::get('/ticket-support', [DashboardController::class, 'showTicketSupport'])->name('ticket-support');

    //submit ticket support
    Route::post('/ticket-support', [DashboardController::class, 'submitTicket'])->name('ticket-support.submit');

    // Edit ticket support detail page
    Route::get('/ticket-support/{id}/edit', [DashboardController::class, 'editTicketSupport'])->name('ticket-support.edit');
    Route::patch('/ticket-support/{id}', [DashboardController::class, 'updateTicketSupport'])->name('ticket-support.update');

    // Start a ticket (open -> in_progress)
    Route::post('/ticket-support/{id}/start', [DashboardController::class, 'startTicket'])->name('ticket-start');

    // Close a ticket (in_progress -> closed)
    Route::post('/ticket-support/{id}/close', [DashboardController::class, 'closeTicket'])->name('ticket-close');

    });


/*
    |--------------------------------------------------------------------------
    | HR MENU
    |--------------------------------------------------------------------------
    | Hanya divisiHR dan IT
    */

    Route::middleware(['division:HR,IT'])->group(function () {

        // READ — daftar karyawan
        Route::get('/employee', [EmployeeController::class, 'index'])
            ->name('employees.index');

        // CREATE — simpan karyawan baru  ← BARU
        Route::post('/employees', [EmployeeController::class, 'store'])
            ->name('employees.store');

        // UPDATE — edit karyawan  ← BARU
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])
            ->name('employees.update');

        // DELETE — hapus karyawan
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])
            ->name('employees.destroy');

        // Resigned
        Route::patch('/employees/{id}/resign', [App\Http\Controllers\EmployeeController::class, 'resign'])
            ->name('employees.resign');

    });

});

// 3. Redirect Root (Opsional)
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});


});


//batas fitur nanti akan di hapus jika sudah tidak dibutuhkan


Route::get('/bmail',[PublicTicketController::class, 'sendBlankMail']);

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');


// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages


Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');