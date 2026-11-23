<?php

use App\Http\Controllers\ItFlowBoardController;
use App\Http\Controllers\ItFlowBoardApiController;
use App\Http\Controllers\ItFlowExportController;
use App\Http\Controllers\ItFlowSessionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/board');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [ItFlowSessionController::class, 'create'])->name('login');
    Route::post('/login', [ItFlowSessionController::class, 'store'])
        ->middleware('throttle:30,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/board', ItFlowBoardController::class)->name('dashboard');
    Route::get('/board/api/meta', [ItFlowBoardApiController::class, 'meta'])->name('board.meta');
    Route::post('/board/api/projects', [ItFlowBoardApiController::class, 'project'])->name('board.projects.store');
    Route::post('/board/api/sub-projects', [ItFlowBoardApiController::class, 'subProject'])->name('board.sub-projects.store');
    Route::post('/board/api/tasks', [ItFlowBoardApiController::class, 'storeTask'])->name('board.tasks.store');
    Route::get('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'showTask'])->name('board.tasks.show');
    Route::patch('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'updateTask'])->name('board.tasks.update');
    Route::patch('/board/api/tasks/{task}/status', [ItFlowBoardApiController::class, 'updateStatus'])->name('board.tasks.status');
    Route::delete('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'destroyTask'])->name('board.tasks.destroy');
    Route::get('/board/api/export', ItFlowExportController::class)->name('board.export');
    Route::post('/logout', [ItFlowSessionController::class, 'destroy'])->name('logout');
});
