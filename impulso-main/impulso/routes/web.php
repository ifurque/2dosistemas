<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/explorar', [DiscoveryController::class, 'index'])->name('discover');
Route::get('/emprendimientos/{business:slug}', [BusinessController::class, 'show'])->name('business.show');
Route::post('/emprendimientos/{business:slug}/turnos', [ManagementController::class, 'appointment'])->name('appointments.store');
Route::get('/turnos/confirmar/{token}', [ManagementController::class, 'confirmAppointment'])->name('appointments.confirm');
Route::post('/emprendimientos/{business:slug}/reseñas', [ReviewController::class, 'store'])->name('reviews.store');
Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/ingresar', [AuthController::class, 'login'])->name('login.store');
    Route::get('/registrarse', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registrarse', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/salir', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/crear-emprendimiento', [BusinessController::class, 'create'])->name('business.create');
    Route::post('/crear-emprendimiento', [BusinessController::class, 'store'])->name('business.store');
    Route::get('/panel/{business:slug}', DashboardController::class)->name('dashboard');
    Route::post('/panel/{business:slug}/productos', [ProductController::class, 'store'])->name('products.store');
    Route::post('/panel/{business:slug}/productos/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock');
    Route::delete('/panel/{business:slug}/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/panel/{business:slug}/gastos', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/panel/{business:slug}/gastos', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/panel/{business:slug}/movimientos', [IncomeController::class, 'index'])->name('movements.index');
    Route::post('/panel/{business:slug}/ingresos', [IncomeController::class, 'store'])->name('incomes.store');
    Route::get('/panel/{business:slug}/gestion', [ManagementController::class, 'index'])->name('management.index');
    Route::get('/panel/{business:slug}/horarios', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/panel/{business:slug}/horarios', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::get('/panel/{business:slug}/publicaciones', [PostController::class, 'index'])->name('posts.index');
    Route::post('/panel/{business:slug}/publicaciones', [PostController::class, 'store'])->name('posts.store');
    Route::put('/panel/{business:slug}/publicaciones/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/panel/{business:slug}/publicaciones/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/panel/{business:slug}/turnos/{appointment}/estado', [ManagementController::class, 'appointmentStatus'])->name('appointments.status');
    Route::post('/panel/{business:slug}/consultas/{inquiry}/estado', [ManagementController::class, 'inquiryStatus'])->name('inquiries.status');
    Route::post('/panel/{business:slug}/redes', [ManagementController::class, 'social'])->name('social.store');
    Route::post('/panel/{business:slug}/miembros', [ManagementController::class, 'member'])->name('members.store');
    Route::post('/emprendimientos/{business:slug}/consultas', [ManagementController::class, 'inquiry'])->name('inquiries.store');
});
