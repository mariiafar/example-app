<?php


use Illuminate\Support\Facades\Route;
use App\Livewire\{Users, Services, Applications, Schedules, ScheduleBrowser, Reviews, WriteReviews, Booking, Report, ClientApplications, UserDeposits, Masters};


Route::view('/', 'welcome');
Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

// Shared between admin+master
Route::middleware(['auth', 'role:admin,master'])->group(function () {
    Route::get('users', Users::class)->name('users');
    Route::get('applications', Applications::class)->name('applications');
    Route::get('schedules', Schedules::class)->name('schedules');
    Route::get('report', Report::class)->name('report');
    Route::get('masters', Masters::class)->name('masters');
});


// Shared between all roles
Route::middleware(['auth', 'role:admin,master,client'])->group(function () {
    Route::get('services', Services::class)->name('services');
    Route::get('schedule-browser', ScheduleBrowser::class)->name('schedule-browser');
    Route::get('reviews', Reviews::class)->name('reviews');
    Route::get('/my-deposits', UserDeposits::class)->name('user.deposits');
    Route::get('/deposits/user/{user}', UserDeposits::class)->name('admin.user-deposits');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('write-reviews', WriteReviews::class)->name('write-reviews');
    Route::get('/booking/{master_id}/{date}/{time}', Booking::class)->name('booking');
    Route::get('/my-appointments', ClientApplications::class)->name('client.appointments');
});

Route::get('/schedule', ScheduleBrowser::class)->name('schedule');

Route::get('/payment/success', function() {
    return view('payment-success');
})->name('payment.success');

Route::get('/payment/{application}', [PaymentController::class, 'show'])
    ->name('payment.show');


Route::post('/payment/{application}/fail', [PaymentController::class, 'fail'])
    ->name('payment.fail');


    


require __DIR__.'/auth.php';
