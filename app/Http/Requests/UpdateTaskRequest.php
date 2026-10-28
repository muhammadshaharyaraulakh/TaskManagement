<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /** Only a logged-in user can edit a task. */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $task = $this->route('task');
        $taskId = is_object($task) ? $task->id : $task;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'name')
                    ->ignore($taskId)
                    ->where(function ($query) use ($task) {
                        $projectId = $this->input('project_id');

                        if ($projectId === null && is_object($task)) {
                            $projectId = $task->project_id;
                        }

                        return $query
                            ->where('user_id', auth()->id())
                            ->where('project_id', (int) $projectId);
                    }),
            ],
            'project_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please give the task a name.',
            'name.unique' => 'This task already exists in the selected project.',
            'project_id.exists' => 'That project does not exist.',
        ];
    }
}
