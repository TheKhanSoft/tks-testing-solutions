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
            'question_text' => 'required|string|max:10000',
            'correct_answer' => 'nullable|string|max:10000',
            'marks' => 'required|integer|min:1',
            'difficulty_level' => [
                'nullable',
                'string',
                'max:255',
                'in:easy,medium,hard,very_hard,expert'
            ],
            'hint' => 'nullable|string|max:1000',
            'explanation' => 'nullable|string|max:2000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
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
            'question_text.max' => 'Question text is too long. Please keep it under 10,000 characters. 📏',
            'correct_answer.string' => 'Correct answer should be text. What\'s the right answer? ✅',
            'correct_answer.max' => 'Correct answer is too long. Keep it under 10,000 characters. 📝',
            'marks.required' => 'Marks are required! How many points is this question worth? 💯',
            'marks.integer' => 'Marks must be a number. Points, please! 🔢',
            'marks.min' => 'Marks should be at least 1. Even small questions have value! ⬆️',
            'difficulty_level.string' => 'Difficulty level should be text. How challenging is this question? ⛰️',
            'difficulty_level.max' => 'Difficulty level is too long. Keep it brief! 🤏',
            'difficulty_level.in' => 'Difficulty level must be one of: easy, medium, hard, very_hard, or expert. 📊',
            'hint.string' => 'Hint should be text. A little nudge in the right direction? ➡️',
            'hint.max' => 'Hint is too long. Keep it under 1000 characters. 💡',
            'explanation.string' => 'Explanation should be text. Why is this the answer? 🤔',
            'explanation.max' => 'Explanation is too long. Keep it under 2000 characters. 📚',
            'tags.array' => 'Tags should be a list of keywords. What topics does this question cover? 🏷️',
            'tags.*.string' => 'Each tag should be text. One word or short phrase per tag please! #️⃣',
            'tags.*.max' => 'Tags should be short - max 50 characters per tag! 📏',
        ];
    }
}