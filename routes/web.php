<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\TodoListController;
use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $lists = TodoList::query()
        ->withCount([
            'tasks',
            'tasks as completed_tasks_count' => function ($q) {
                $q->where('completed', true);
            },
        ])
        ->latest()
        ->get();

    $recentTasks = Task::query()->with('list:id,name,color')->latest()->get();
    $totalTasks = Task::count();
    $completedTasks = Task::where('completed', true)->count();
    $pendingTasks = Task::where('completed', false)->count();

    return Inertia::render('Dashboard', [
        'lists' => $lists,
        'recentTasks' => $recentTasks,
        'totalTasks' => $totalTasks,
        'completedTasks' => $completedTasks,
        'pendingTasks' => $pendingTasks,
    ]);
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('lists', TodoListController::class);
    Route::resource('tasks', TaskController::class);
});

require __DIR__ . '/settings.php';
