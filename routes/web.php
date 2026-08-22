<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/dashboard', '/chat')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/chat', [ChatController::class, 'show'])
    ->middleware(['auth', 'last.seen'])
    ->name('chat');

Route::post('/chat/send', [ChatController::class, 'store'])
    ->middleware(['auth', 'last.seen'])
    ->name('chat.send');

Route::post('/chat/read', [ChatController::class, 'markRead'])
    ->middleware(['auth', 'last.seen'])
    ->name('chat.read');

Route::middleware(['auth', 'last.seen'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
