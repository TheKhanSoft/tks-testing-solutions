<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $this->department, // Unique except for the current department being updated
            'description' => 'nullable|string',
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
        ];
    }
}