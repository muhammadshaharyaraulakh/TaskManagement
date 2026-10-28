<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()
            ->with('project:id,name')
            ->when($request->filled('project_id'), function ($query) use ($request) {
                $query->where('project_id', $request->integer('project_id'));
            })
            ->orderBy('project_id')
            ->orderBy('priority')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();

        $nextPriority = $request->user()->tasks()
            ->where('project_id', $data['project_id'])
            ->max('priority');

        $task = $request->user()->tasks()->create([
            'name' => $data['name'],
            'project_id' => $data['project_id'],
            'priority' => ($nextPriority ?? 0) + 1,
        ]);

        $task->load('project:id,name');

        return response()->json([
            'message' => 'Task created.',
            'task' => $task,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->checkOwner($request, $task);

        $data = $request->validated();
        $oldProjectId = $task->project_id;

        if (isset($data['project_id']) && (int) $data['project_id'] !== (int) $oldProjectId) {
            $data['priority'] = ($request->user()->tasks()
                ->where('project_id', $data['project_id'])
                ->max('priority') ?? 0) + 1;
        }

        $task->update($data);

        if (isset($data['project_id']) && (int) $data['project_id'] !== (int) $oldProjectId) {
            $this->renumberTasks($request->user()->id, (int) $oldProjectId);
        }

        $task->load('project:id,name');

        return response()->json([
            'message' => 'Task updated.',
            'task' => $task,
        ]);
    }


    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->checkOwner($request, $task);

        $projectId = $task->project_id;
        $userId = $request->user()->id;

        $task->delete();

        $this->renumberTasks($userId, (int) $projectId);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function reorder(ReorderTasksRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($data, $user) {

            $nextPriorityByProject = [];

            foreach ($data['task_ids'] as $taskId) {
                $task = $user->tasks()->whereKey($taskId)->first();

                if (! $task) {
                    continue;
                }

                if (! empty($data['project_id']) && (int) $task->project_id !== (int) $data['project_id']) {
                    continue;
                }

                $projectId = (int) $task->project_id;
                $nextPriorityByProject[$projectId] = ($nextPriorityByProject[$projectId] ?? 0) + 1;

                $task->update(['priority' => $nextPriorityByProject[$projectId]]);
            }
        });

        return response()->json(['message' => 'Order saved.']);
    }


    private function checkOwner(Request $request, Task $task): void
    {
        abort_if($task->user_id !== $request->user()->id, 403, 'This task is not yours.');
    }

    /**
     * Renumber the tasks in a project after one has been deleted or moved.
     * This ensures that the priorities are sequential and start from 1.
     */
    private function renumberTasks(int $userId, int $projectId): void
    {
        $tasks = Task::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->orderBy('priority')
            ->get();

        foreach ($tasks as $index => $task) {
            $task->update(['priority' => $index + 1]);
        }
    }
}
