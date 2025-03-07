<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionFormRequest extends FormRequest
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
            'subject_id' => 'required|exists:subjects,id',
            'question_type_id' => 'required|exists:question_types,id',
            'question_text' => 'required|string',
            'correct_answer' => 'nullable|string', // Adjust validation based on question type if needed
            'marks' => 'required|integer|min:1',
            'difficulty_level' => 'nullable|string|max:255',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
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
            'subject_id.required' => 'Subject is required! Which subject does this question belong to? 📚',
            'subject_id.exists' => 'Selected subject is invalid. Let\'s choose a subject from the list. 🧐',
            'question_type_id.required' => 'Question type is needed! Is it MCQ, written, or what? ❓',
            'question_type_id.exists' => 'Selected question type is not valid. Pick a question type from the options. 🤔',
            'question_text.required' => 'Question text is a must! What’s the big question? 📝',
            'correct_answer.string' => 'Correct answer should be text. What\'s the right answer? ✅',
            'marks.required' => 'Marks are required! How many points is this question worth? 💯',
            'marks.integer' => 'Marks must be a number. Points, please! 🔢',
            'marks.min' => 'Marks should be at least 1.  Even small questions have value!  ⬆️',
            'difficulty_level.string' => 'Difficulty level should be text. How challenging is this question? ⛰️',
            'difficulty_level.max' => 'Difficulty level is too long. Keep it brief!  🤏',
            'hint.string' => 'Hint should be text.  A little nudge in the right direction?  ➡️',
            'explanation.string' => 'Explanation should be text.  Why is this the answer?  🤔',
        ];
    }
}