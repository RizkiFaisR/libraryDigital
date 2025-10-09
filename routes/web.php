<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\BorrowingController;

// Static Routes
Route::get('/', function () {
    return view('welcome');
});
Route::get('books', [BookController::class, 'index'])->name('books.index');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::get('register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
});

// Auth Routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Admin and Operator Routes
    Route::middleware('role:admin,operator')->group(function () {
        Route::resource('books', BookController::class)->except(['index']);
        Route::resource('categories', CategoryController::class);
        Route::resource('publishers', PublisherController::class);
        Route::resource('borrowings', BorrowingController::class);
        Route::post('borrowings/{id}/approve', [BorrowingController::class, 'approve'])->name('borrowings.approve');
        Route::delete('borrowings/{id}/reject', [BorrowingController::class, 'reject'])->name('borrowings.reject');
    });

    // User Routes
    Route::middleware('role:user')->group(function () {
        Route::get('my-borrowings', [BorrowingController::class, 'myBorrowings'])->name('user.borrowings');
        Route::post('borrow-book', [BorrowingController::class, 'borrowBook'])->name('user.borrow');
    });
});
