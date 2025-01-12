<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WhatsAppController;

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

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// Phone Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-phone', [AuthController::class, 'showPhoneVerification'])->name('phone.verify');
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone'])->name('phone.verify.submit');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('phone.verify.resend');
});

// Update the dashboard route to include phone verification middleware
Route::middleware(['auth', 'phone.verified'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// WhatsApp Setup Routes
Route::prefix('whatsapp')->group(function () {
    Route::get('/setup', [WhatsAppController::class, 'setup'])->name('whatsapp.setup');
    Route::post('/create-instance', [WhatsAppController::class, 'createInstance'])->name('whatsapp.create');
    Route::get('/qr', [WhatsAppController::class, 'showQR'])->name('whatsapp.qr');
    Route::get('/check-connection', [WhatsAppController::class, 'checkConnection'])->name('whatsapp.check-connection');
});
