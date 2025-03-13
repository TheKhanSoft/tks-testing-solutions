<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class QuestionTypeFormRequest extends FormRequest
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
        $questionTypeId = $this->route('question_type') ? $this->route('question_type')->id : $this->question_type;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:question_types,name,{$questionTypeId}"
            ],
            'description' => 'nullable|string|max:1000',
            'has_options' => 'sometimes|boolean',
            'requires_manual_grading' => 'sometimes|boolean',
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
            'name.required' => 'Question type needs a name! What kind of question is this? 🤔',
            'name.unique' => 'Question type name already exists. Let\'s find a unique name! ✨',
            'name.max' => 'Question type name is too long. Keep it short and sweet! 🍬',
            'description.string' => 'Question type description should be text. Tell us more! ✍️',
            'description.max' => 'Description is getting quite lengthy. Keep it under 1000 characters! 📚',
            'has_options.boolean' => 'Options flag must be true or false. Does this question type have selectable options? 🎯',
            'requires_manual_grading.boolean' => 'Manual grading flag must be true or false. Will teachers need to grade this manually? 👨‍🏫',
        ];
    }
}