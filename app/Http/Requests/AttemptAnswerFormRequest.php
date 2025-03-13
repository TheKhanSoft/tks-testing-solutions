<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttemptAnswerFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Get the attempt answer ID for update scenarios
        $attemptAnswerId = $this->route('attempt_answer') ? $this->route('attempt_answer')->id : null;
        
        return [
            'attempt_id' => 'required|exists:attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'nullable|exists:question_options,id',
            'answer_text' => 'nullable|string|max:10000',
            'is_correct' => 'nullable|boolean',
            'marks_obtained' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string|max:2000',
            'graded_by' => 'nullable|exists:users,id',
            'graded_at' => 'nullable|date',
            'time_spent_seconds' => 'nullable|integer|min:0',
            'is_flagged' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    protected function attributes(): array
    {
        return [
            'attempt_id' => 'attempt',
            'question_id' => 'question',
            'selected_option_id' => 'selected option',
            'graded_by' => 'grader',
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
            'attempt_id.required' => 'Attempt ID is needed! Which test attempt does this answer belong to? 📝',
            'attempt_id.exists' => 'Invalid attempt ID. This attempt doesn\'t exist in our records. 🧐',
            'question_id.required' => 'Question ID is essential! Which question is being answered? ❓',
            'question_id.exists' => 'That question doesn\'t exist in our database. Let\'s check the question ID! 🔍',
            'selected_option_id.exists' => 'Selected option isn\'t valid. Choose from available options! 🎯',
            'answer_text.string' => 'Answer text should be... well, text! What\'s your response? ✍️',
            'answer_text.max' => 'Answer text is too long. Keep it under 10,000 characters! 📚',
            'is_correct.boolean' => 'Correctness must be true or false. Is this answer right or wrong? ✅❌',
            'marks_obtained.numeric' => 'Marks must be a number. How many points for this answer? 💯',
            'marks_obtained.min' => 'Marks can\'t be negative. Even zero is better than negative! 📈',
            'feedback.string' => 'Feedback should be text. Any comments about this answer? 💬',
            'feedback.max' => 'Feedback is too long. Keep it under 2000 characters! 📝',
            'graded_by.exists' => 'Invalid grader ID. This user doesn\'t exist in our system! 👨‍🏫',
            'graded_at.date' => 'Grading time must be a valid date and time. When was this answer graded? 🕒',
            'time_spent_seconds.integer' => 'Time spent must be a whole number of seconds. How long did it take? ⏱️',
            'time_spent_seconds.min' => 'Time spent can\'t be negative. Time only moves forward! ⏩',
            'is_flagged.boolean' => 'Flag status must be true or false. Do we need to review this answer? 🚩',
        ];
    }
}
