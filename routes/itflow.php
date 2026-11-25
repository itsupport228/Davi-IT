<?php

use App\Http\Controllers\ItFlowBoardController;
use App\Http\Controllers\ItFlowBoardApiController;
use App\Http\Controllers\ItFlowExportController;
use App\Http\Controllers\ItFlowSessionController;
use App\Http\Controllers\ItFlowDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/board');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [ItFlowSessionController::class, 'create'])->name('login');
    Route::post('/login', [ItFlowSessionController::class, 'store'])
        ->middleware('throttle:30,1')->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/board', ItFlowBoardController::class)->name('dashboard');
    Route::get('/dashboard', [ItFlowDashboardController::class, 'index'])->name('atl.dashboard');
    Route::get('/dashboard/api', [ItFlowDashboardController::class, 'data'])->name('atl.dashboard.data');
    Route::get('/board/api/meta', [ItFlowBoardApiController::class, 'meta'])->name('board.meta');
    Route::get('/board/api/users', [ItFlowBoardApiController::class, 'users'])->name('board.users.index');
    Route::post('/board/api/users', [ItFlowBoardApiController::class, 'storeUser'])->name('board.users.store');
    Route::patch('/board/api/users/{user}', [ItFlowBoardApiController::class, 'updateUser'])->name('board.users.update');
    Route::delete('/board/api/users/{user}', [ItFlowBoardApiController::class, 'destroyUser'])->name('board.users.destroy');
    Route::post('/board/api/projects', [ItFlowBoardApiController::class, 'project'])->name('board.projects.store');
    Route::delete('/board/api/projects/{project}', [ItFlowBoardApiController::class, 'destroyProject'])->name('board.projects.destroy');
    Route::post('/board/api/sub-projects', [ItFlowBoardApiController::class, 'subProject'])->name('board.sub-projects.store');
    Route::delete('/board/api/sub-projects/{subProject}', [ItFlowBoardApiController::class, 'destroySubProject'])->name('board.sub-projects.destroy');
    Route::post('/board/api/tasks', [ItFlowBoardApiController::class, 'storeTask'])->name('board.tasks.store');
    Route::get('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'showTask'])->name('board.tasks.show');
    Route::patch('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'updateTask'])->name('board.tasks.update');
    Route::patch('/board/api/tasks/{task}/status', [ItFlowBoardApiController::class, 'updateStatus'])->name('board.tasks.status');
    Route::delete('/board/api/tasks/{task}', [ItFlowBoardApiController::class, 'destroyTask'])->name('board.tasks.destroy');
    Route::get('/board/api/export', ItFlowExportController::class)->name('board.export');
    Route::post('/logout', [ItFlowSessionController::class, 'destroy'])->name('logout');
});
