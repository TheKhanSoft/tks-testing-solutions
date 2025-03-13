<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaperQuestionFormRequest extends FormRequest
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
            'paper_id' => 'required|exists:papers,id',
            'question_id' => 'required|exists:questions,id',
            'order_number' => 'nullable|integer|min:1',
            'marks_allocated' => 'nullable|numeric|min:0',
            'section_name' => 'nullable|string|max:255',
            'is_optional' => 'sometimes|boolean',
            'instructions' => 'nullable|string|max:1000',
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
            'paper_id' => 'paper',
            'question_id' => 'question',
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
            'paper_id.required' => 'Paper ID is needed! Which paper are we adding questions to? 📝',
            'paper_id.exists' => 'Invalid paper selected. Choose a paper from the list! 📋',
            'question_id.required' => 'Question ID is required! Which question are we including? ❓',
            'question_id.exists' => 'That question doesn\'t exist in our database. Let\'s pick a real one! 🧐',
            'order_number.integer' => 'Question order must be a whole number. Where should this question appear? 🔢',
            'order_number.min' => 'Question order must be at least 1. First things first! 1️⃣',
            'marks_allocated.numeric' => 'Marks must be a number. How many points is this question worth? 💯',
            'marks_allocated.min' => 'Marks cannot be negative. Let\'s be positive here! ➕',
            'section_name.max' => 'Section name is too long. Keep it under 255 characters! 📏',
            'is_optional.boolean' => 'Optional flag must be true or false. Is this question mandatory or optional? ⭐',
            'instructions.max' => 'Instructions are too long. Keep it under 1000 characters! 📚',
        ];
    }
}
