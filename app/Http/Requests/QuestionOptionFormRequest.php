<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionOptionFormRequest extends FormRequest
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
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string',
            'is_correct' => 'sometimes|boolean', // Optional, defaults to false if not provided
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
            'question_id.required' => 'Question ID is required! Which question does this option belong to? ❓',
            'question_id.exists' => 'Invalid question ID.  Make sure it\'s a real question! 🧐',
            'option_text.required' => 'Option text is needed! What is this option saying?  💬',
            'is_correct.boolean' => 'Correct option must be true or false. Is it the right one? ✅ or ❌?',
        ];
    }
}