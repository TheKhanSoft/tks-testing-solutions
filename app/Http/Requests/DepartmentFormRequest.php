<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class DepartmentFormRequest extends FormRequest
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
            $this->merge([
                'name' => Str::title($this->name),
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
        $departmentId = $this->route('department') ? $this->route('department')->id : $this->department;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:departments,name,{$departmentId}"
            ],
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:20|unique:departments,code,' . $departmentId,
            'head_id' => 'nullable|exists:faculty_members,id',
            'is_active' => 'sometimes|boolean',
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
            'name.required' => 'Department name? We need a cool name for this awesome department! ✨',
            'name.unique' => 'Oops! Looks like a department with that name already exists. Let\'s get a bit more creative! 💡',
            'name.max' => 'Whoa there! Department names shouldn\'t be longer than 255 characters. Let\'s keep it concise. 😉',
            'description.string' => 'Department description should be text. Tell us a story! ✍️',
            'description.max' => 'Description is getting lengthy! Keep it under 1000 characters. 📝',
            'code.max' => 'Department code should be short - max 20 characters! 📊',
            'code.unique' => 'This department code is already in use. Each department needs a unique code! 🔢',
            'head_id.exists' => 'The selected department head doesn\'t exist in our faculty records. Choose a valid faculty member! 🧑‍🏫',
            'is_active.boolean' => 'Active status must be true or false. Is this department operational? ✅❌',
        ];
    }
}