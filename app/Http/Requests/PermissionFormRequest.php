<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PermissionFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->name) {
            // Convert to snake_case for consistent permission naming
            $this->merge([
                'name' => Str::snake($this->name),
            ]);
        }
        
        if ($this->group) {
            $this->merge([
                'group' => Str::snake($this->group),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $permissionId = $this->route('permission') ? $this->route('permission')->id : $this->permission;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_\.]+$/',
                "unique:permissions,name,{$permissionId}"
            ],
            'description' => 'nullable|string|max:1000',
            'group' => 'nullable|string|max:100|regex:/^[a-z0-9_\.]+$/',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is needed! What action does this permission allow? 🔑',
            'name.unique' => 'This permission name already exists. Let\'s be original! ✨',
            'name.max' => 'Permission name is too long. Keep it under 255 characters! 📏',
            'name.regex' => 'Permission name should only contain lowercase letters, numbers, dots and underscores. 🔤',
            'description.string' => 'Description should be text. What does this permission actually do? 🤔',
            'description.max' => 'Description is too long. Keep it under 1000 characters! 📚',
            'group.string' => 'Group should be text. Which category does this permission belong to? 📁',
            'group.max' => 'Group name is too long. Keep it under 100 characters! ✂️',
            'group.regex' => 'Group should only contain lowercase letters, numbers, dots and underscores. 🔤',
        ];
    }
}
