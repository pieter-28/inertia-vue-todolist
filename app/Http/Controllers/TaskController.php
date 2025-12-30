<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Task::with('list:id,name,color');
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        $tasks = $query->latest()->paginate(10)->withQueryString();
        $lists = TodoList::select('id', 'name', 'color')->get();
        return Inertia::render('task/Index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => $request->only(['search', 'priority', 'list_id']),
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
    public function store(Request $request): RedirectResponse
    {
        $validate = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:16',
            'completed' => 'nullable|boolean',
            'list_id' => 'required|exists:lists,id',
        ]);

        $validate['completed'] = (bool) ($validate['completed'] ?? false);
        $validate['priority'] = $validate['priority'] ?? 'normal';
        Task::create($validate);
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validate = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:16',
            'completed' => 'nullable|boolean',
        ]);

        $validate['completed'] = (bool) ($validate['completed'] ?? $task->completed);
        $validate['priority'] = $validate['priority'] ?? $task->priority;
        $task->update($validate);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();
        return redirect()->back();
    }
}
