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
        return true;
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
            'option_text' => 'required|string|max:1000',
            'is_correct' => 'sometimes|boolean',
            'order' => 'nullable|integer|min:0',
            'explanation' => 'nullable|string|max:1000',
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
            'question_id.exists' => 'Invalid question ID. Make sure it\'s a real question! 🧐',
            'option_text.required' => 'Option text is needed! What is this option saying? 💬',
            'option_text.max' => 'Option text is too long. 1000 characters is the maximum! 📝',
            'is_correct.boolean' => 'Correct option must be true or false. Is it the right one? ✅ or ❌?',
            'order.integer' => 'Order must be a number. Where should this option appear in the list? 🔢',
            'order.min' => 'Order cannot be negative. Let\'s start from 0 or higher! 📊',
            'explanation.string' => 'Explanation should be text. Why is this answer right or wrong? 🤔',
            'explanation.max' => 'Explanation is too long. Keep it under 1000 characters! 📚',
        ];
    }
}