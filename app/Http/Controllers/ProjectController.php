<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()->projects()
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return response()->json(['projects' => $projects]);
    }

    /* Create a project  */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $request->user()->projects()->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Project created.',
            'project' => $project,
        ], 201);
    }

    /* Delete a project*/
    public function destroy(Request $request, Project $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403, 'This project is not yours.');

        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }
}
