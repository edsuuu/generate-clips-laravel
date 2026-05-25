<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

use App\Models\Video;

// Fluxo de geração de cortes (sem auth, conforme solicitado).
Route::view('/videos/create', 'videos.create')->name('videos.create');

Route::get('/videos/{video}/transcript', fn(Video $video) => view('videos.transcript', ['video' => $video]))->name('videos.transcript');

Route::get('/videos/{video}/editor', fn(Video $video) => view('videos.editor', ['video' => $video]))->name('videos.editor');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
