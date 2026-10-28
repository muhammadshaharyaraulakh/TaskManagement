<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /** Only a logged-in user can create a project. */
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
                Rule::unique('projects', 'name')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please give the project a name.',
            'name.unique' => 'You already have a project with this name.',
        ];
    }
}
