<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TodoListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('list/Index', [
            'lists' => TodoList::query()->select('id', 'name', 'color', 'created_at')->withCount('tasks')->latest()->get(),
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
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
        ]);

        TodoList::create($validate);
        return redirect()->route('lists.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(TodoList $list)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TodoList $list)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TodoList $list): RedirectResponse
    {
        $list->update(
            $request->validate([
                'name' => 'required',
                'color' => 'required',
            ]),
        );
        return redirect()->route('lists.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TodoList $list)
    {
        $list->delete();
        return redirect()->route('lists.index');
    }
}
