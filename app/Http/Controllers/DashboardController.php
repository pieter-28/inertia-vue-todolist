<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lists = TodoList::query()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => $q->where('completed', true)])
            ->latest()
            ->take(10) // untuk preview dashboard
            ->get();

        return Inertia::render('Dashboard', [
            'lists' => $lists,
            'recentTasks' => Task::query()->with('list:id,name,color')->latest()->take(10)->get(),
            'totalLists' => TodoList::count(), // ✅ FIX
            'totalTasks' => Task::count(),
            'completedTasks' => Task::where('completed', true)->count(),
            'pendingTasks' => Task::where('completed', false)->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
