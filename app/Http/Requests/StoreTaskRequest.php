<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /** Only a logged-in user can create a task. */
    public function authorize(): bool
    {
        return auth()->check();
    }
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'name')->where(function ($query) {
                    return $query
                        ->where('user_id', auth()->id())
                        ->where('project_id', $this->integer('project_id'));
                }),
            ],
            'project_id' => [
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
            'project_id.required' => 'Please choose a project.',
            'project_id.exists' => 'That project does not exist.',
        ];
    }
}
