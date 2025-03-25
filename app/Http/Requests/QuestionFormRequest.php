<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from text fields
        $this->merge([
            'text' => Str::of($this->text)->trim(),
            'description' => Str::of($this->description)->trim(),
            'explanation' => Str::of($this->explanation)->trim(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'question_type_id' => ['required', 'exists:question_types,id'],
            'max_time_allowed' => ['nullable', 'integer', 'min:0'],
            'negative_marks' => ['nullable', 'numeric', 'min:0'],
            'text' => ['required', 'string', 'max:10000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:2048'],
            'marks' => ['required', 'integer', 'min:1'],
            'difficulty_level' => [
                'required',
                'string',
                'max:255',
                'in:easy,medium,hard,very_hard,expert',
            ],
            'status' => [
                'required',
                'string',
                'max:255',
                'in:active,inactive',
            ],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.text' => ['required', 'string'],
            'options.*.is_correct' => ['boolean'],
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
            'subject_id.required' => 'Subject is missing! Which subject does this question belong to? 🧪📚',
            'subject_id.exists' => 'Whoops! That subject doesn\'t exist in our system. New discovery? 🌍',

            'question_type_id.required' => 'Question type required! Multiple choice? True/False? Help us out! 🔍',
            'question_type_id.exists' => 'That question type isn\'t recognized. Innovating something new? 🚀',

            'max_time_allowed.integer' => 'Max time allowed must be a whole number. No fractions or decimals! ⏳',
            'max_time_allowed.min' => 'Max time allowed must be at least 1 second. Time is precious! ⏰',

            'negative_marks.numeric' => 'Negative marks must be a number. Let\'s keep it fair! 📉',
            'negative_marks.min' => 'Negative marks can\'t be less than 0. No double penalties! 🚫',

            'text.required' => 'Hold on! The question text can\'t be empty. What\'s the query? 🤔',
            'text.string' => 'Question text should be a proper sentence, not hieroglyphics! 📜',
            'text.max' => 'Question text is too long! Keep it under 10,000 characters. 📝',

            'description.string' => 'Description should be text. Tell us more about the question! ✍️',
            'description.max' => 'Description is getting lengthy! Keep it under 10,000 characters. 📚',

            'explanation.string' => 'Explanation should be text. Help us understand the reasoning! 🧠',
            'explanation.max' => 'Explanation is too long! Keep it under 2,000 characters. 📖',

            'image.string' => 'Image URL must be a valid string. No funny business! 🖼️',
            'image.max' => 'Image URL is too long! Keep it under 2,048 characters. 📏',

            'marks.required' => 'Marks matter! How many points is this question worth? 🎯',
            'marks.integer' => 'Marks must be a whole number. No fractions or decimals! 🔢',
            'marks.min' => 'Marks can\'t be less than 1. Let\'s value this question properly! ⭐',

            'difficulty_level.string' => 'Difficulty level must be a string. Easy, medium, or hard? 💪',
            'difficulty_level.max' => 'Difficulty level is too long! Keep it under 255 characters. 📏',
            'difficulty_level.in' => 'Difficulty must be easy, medium, hard, very hard, or expert. No "impossible" setting! 😅',

            'status.required' => 'Active or inactive? Let us know the question\'s status! 🔄',
            'status.string' => 'Status must be a string. Active or inactive? 🤷‍♂️',
            'status.max' => 'Status is too long! Keep it under 255 characters. 📏',
            'status.in' => 'Status must be "active" or "inactive". No limbo states! 😉',

            'tags.array' => 'Tags must be provided as a list. Time to categorize! 🏷️',
            'tags.*.string' => 'Each tag must be text. No emoji-only tags! 🚫🎨',
            'tags.*.max' => 'Tags are too long! Keep each under 50 characters. 📏',

            'options.array' => 'Options must be provided as a list. Time to brainstorm choices! 💡',
            'options.*.text.required' => 'Option text is missing! Every choice needs some content. 📝',
            'options.*.text.string' => 'Options must be text-based. Emoji-only answers won\'t work! 🚫🎨',
            'options.*.is_correct.boolean' => 'For "is_correct", just tell us yes (true) or no (false)! 👍👎',
        ];
    }
}