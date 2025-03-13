<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PaperFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->name) {
            $this->merge([
                'name' => Str::title($this->name),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $paperId = $this->route('paper') ? $this->route('paper')->id : $this->paper;
        
        return [
            'subject_id' => 'required|exists:subjects,id',
            'paper_category_id' => 'required|exists:paper_categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:papers,name,{$paperId},id,subject_id,{$this->subject_id}"
            ],
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'total_marks' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string|max:5000',
            'is_published' => 'sometimes|boolean',
            'published_at' => 'nullable|date|after_or_equal:today',
            'access_code' => 'nullable|string|max:20',
            'passing_percentage' => 'nullable|integer|min:0|max:100',
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
            'subject_id.exists' => 'Invalid subject selected. Choose a subject from the list! 🧐',
            'paper_category_id.required' => 'Paper category is needed! Is it a Midterm, Final, or Practice? 🤔',
            'paper_category_id.exists' => 'Invalid paper category selected. Pick a category from the options! 🧐',
            'name.required' => 'Paper name is a must! What should we call this paper? 📝',
            'name.max' => 'Paper name is too long. Keep it under 255 characters! ✂️',
            'name.unique' => 'A paper with this name already exists for this subject. Let\'s be more creative! ✨',
            'description.string' => 'Paper description should be text. Describe the paper briefly! ✍️',
            'description.max' => 'Description is too long. Keep it under 2000 characters! 📚',
            'duration_minutes.integer' => 'Duration should be a number in minutes. How long is the test? ⏱️',
            'duration_minutes.min' => 'Duration should be at least 1 minute. Tests take time! ⏳',
            'duration_minutes.max' => 'Duration seems too long. 600 minutes (10 hours) is the maximum! ⏰',
            'total_marks.integer' => 'Total marks should be a number. What\'s the total score? 💯',
            'total_marks.min' => 'Total marks should be at least 1. Every mark counts! ⬆️',
            'instructions.string' => 'Instructions should be text. Tell users what to do! 📖',
            'instructions.max' => 'Instructions are too long. Keep them under 5000 characters! 📏',
            'is_published.boolean' => 'Published status must be true or false. Is it ready to go live? 🚀 or 🚧?',
            'published_at.date' => 'Published date must be a valid date. When should this go live? 📅',
            'published_at.after_or_equal' => 'Published date cannot be in the past. Future or today only! ⏭️',
            'access_code.max' => 'Access code is too long. Keep it under 20 characters! 🔐',
            'passing_percentage.integer' => 'Passing percentage must be a number. What score is needed to pass? 📊',
            'passing_percentage.min' => 'Passing percentage cannot be negative. 0% or higher, please! 📈',
            'passing_percentage.max' => 'Passing percentage cannot exceed 100%. That\'s impossible to achieve! 💯',
        ];
    }
}