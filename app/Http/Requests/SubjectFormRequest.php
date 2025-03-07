<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectFormRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:subjects,name,' . $this->subject, // Unique except for the current subject being updated
            'department_id' => 'required|exists:departments,id',
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
            'name.required' => 'Subject name is a must! What should we call this subject? 🤔',
            'name.unique' => 'Subject name is already taken. How about a slightly different title? ✨',
            'name.max' => 'Subject name is too long. Keep it under 255 characters, please! 📏',
            'department_id.required' => 'Department is required! Which department does this subject belong to? 🏢',
            'department_id.exists' => 'Hmm, the selected department doesn\'t seem to exist. Let\'s double-check! 🧐',
            'description.string' => 'Subject description should be text. Paint a picture with words! 🖼️',
        ];
    }
}