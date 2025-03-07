<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerFormRequest extends FormRequest
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
            'test_attempt_id' => 'required|exists:test_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'user_answer' => 'nullable|string', // Adjust validation based on question type if needed
            'is_correct' => 'sometimes|boolean',
            'marks_obtained' => 'nullable|integer',
            'time_spent_seconds' => 'nullable|integer|min:0', // Time spent should not be negative
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
            'test_attempt_id.required' => 'Test Attempt ID is required! Which attempt does this answer belong to? 📝',
            'test_attempt_id.exists' => 'Invalid test attempt ID.  Make sure it\'s a valid test attempt! 🧐',
            'question_id.required' => 'Question ID is needed! Which question is being answered? ❓',
            'question_id.exists' => 'Invalid question ID.  Pick a question from the paper! 🧐',
            'user_answer.string' => 'User answer should be text. What did they answer? 💬',
            'is_correct.boolean' => 'Correct status must be true or false.  Was it the right answer? ✅ or ❌?',
            'marks_obtained.integer' => 'Marks obtained should be a number. How many points did they get? 🔢',
            'time_spent_seconds.integer' => 'Time spent should be a number in seconds. How long did they take? ⏱️',
            'time_spent_seconds.min' => 'Time spent cannot be negative. Time only moves forward! ➡️',
        ];
    }
}