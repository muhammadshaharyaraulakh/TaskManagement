<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    
    public function index(Request $request): View
    {
        $user = $request->user();

        $projects = $user->projects()
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        $selectedProjectId = $request->integer('project_id') ?: null;

        $tasks = $user->tasks()
            ->with('project:id,name')
            ->when($selectedProjectId, function ($query) use ($selectedProjectId) {
                $query->where('project_id', $selectedProjectId);
            })
            ->orderBy('project_id')
            ->orderBy('priority')
            ->get();

        return view('dashboard', [
            'projects' => $projects,
            'tasks' => $tasks,
            'selectedProjectId' => $selectedProjectId,
        ]);
    }
}
