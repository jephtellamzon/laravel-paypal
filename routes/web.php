<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('paypal', [PaypalController::class, 'paypal'])->middleware(['auth', 'verified'])->name('paypal');

Route::get('success', [PaypalController::class, 'success'])->middleware(['auth', 'verified'])->name('success');

Route::get('cancel', [PaypalController::class, 'cancel'])->middleware(['auth', 'verified'])->name('cancel');

Route::get('completed', function () {
    return view('completed');
})->middleware(['auth', 'verified'])->name('completed');

Route::get('cancelled', function () {
    return view('cancelled');
})->middleware(['auth', 'verified'])->name('cancelled');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
