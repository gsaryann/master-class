<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\MasterClassController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MasterClassController::class, 'index'])->name('home');
Route::get('/craft-types/{craftType:slug}', [MasterClassController::class, 'show'])->name('craft-types.show');

Route::middleware('session.guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('session.user')->group(function (): void {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/cabinet', [CabinetController::class, 'index'])->name('cabinet');

    Route::get('/master-classes/{masterClass}/booking', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::post('/master-classes/{masterClass}/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/master-classes/{masterClass}/booking-cancel', [BookingController::class, 'confirmCancel'])->name('booking.cancel');
    Route::post('/master-classes/{masterClass}/booking-cancel', [BookingController::class, 'cancel'])->name('booking.cancel.submit');
});

Route::middleware('session.master')->group(function (): void {
    Route::get('/master-classes/create', [MasterClassController::class, 'create'])->name('master-classes.create');
    Route::post('/master-classes', [MasterClassController::class, 'store'])->name('master-classes.store');
    Route::get('/master-classes/{masterClass}/edit', [MasterClassController::class, 'edit'])->name('master-classes.edit');
    Route::put('/master-classes/{masterClass}', [MasterClassController::class, 'update'])->name('master-classes.update');
    Route::get('/master-classes/occupied-slots', [MasterClassController::class, 'occupiedSlots'])->name('master-classes.occupied-slots');
});
