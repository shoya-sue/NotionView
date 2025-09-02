<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotionController;

Route::get('/', [NotionController::class, 'index'])->name('home');
Route::get('/page/{pageId}', [NotionController::class, 'show'])->name('notion.show');