<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaperFormRequest extends FormRequest
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
            'paper_category_id' => 'required|exists:paper_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_marks' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'is_published' => 'sometimes|boolean', // For publishing status
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
            'subject_id.required' => 'Subject is required! Which subject is this paper for? 📚',
            'subject_id.exists' => 'Invalid subject selected.  Choose a subject from the list! 🧐',
            'paper_category_id.required' => 'Paper category is needed! Is it a Midterm, Final, or Practice? 🤔',
            'paper_category_id.exists' => 'Invalid paper category selected.  Pick a category from the options! 🧐',
            'name.required' => 'Paper name is a must! What should we call this paper? 📝',
            'name.max' => 'Paper name is too long.  Keep it under 255 characters! ✂️',
            'description.string' => 'Paper description should be text.  Describe the paper briefly! ✍️',
            'duration_minutes.integer' => 'Duration should be a number in minutes. How long is the test? ⏱️',
            'duration_minutes.min' => 'Duration should be at least 1 minute.  Tests take time! ⏳',
            'total_marks.integer' => 'Total marks should be a number. What\'s the total score? 💯',
            'total_marks.min' => 'Total marks should be at least 1. Every mark counts! ⬆️',
            'instructions.string' => 'Instructions should be text.  Tell users what to do!  📖',
            'is_published.boolean' => 'Published status must be true or false. Is it ready to go live?  🚀 or 🚧?',
        ];
    }
}