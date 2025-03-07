<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionTypeFormRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:question_types,name,' . $this->question_type, // Unique except for the current question type being updated
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
            'name.required' => 'Question type needs a name! What kind of question is this? 🤔',
            'name.unique' => 'Question type name already exists. Let\'s find a unique name! ✨',
            'name.max' => 'Question type name is too long. Keep it short and sweet! 🍬',
            'description.string' => 'Question type description should be text. Tell us more! ✍️',
        ];
    }
}