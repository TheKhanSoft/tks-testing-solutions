<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SubjectFormRequest extends FormRequest
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
        $subjectId = $this->route('subject') ? $this->route('subject')->id : $this->subject;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:subjects,name,{$subjectId}"
            ],
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:20',
            'credits' => 'nullable|integer|min:1|max:6',
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
            'name.required' => 'Subject name is a must! What should we call this subject? 🤔',
            'name.unique' => 'Subject name is already taken. How about a slightly different title? ✨',
            'name.max' => 'Subject name is too long. Keep it under 255 characters, please! 📏',
            'department_id.required' => 'Department is required! Which department does this subject belong to? 🏢',
            'department_id.exists' => 'Hmm, the selected department doesn\'t seem to exist. Let\'s double-check! 🧐',
            'description.string' => 'Subject description should be text. Paint a picture with words! 🖼️',
            'description.max' => 'Description is getting quite lengthy. Keep it under 1000 characters! 📚',
            'code.max' => 'Subject code should be short and sweet - max 20 characters! 📝',
            'credits.integer' => 'Credits must be a whole number. How many credits is this subject worth? 🎓',
            'credits.min' => 'Subject should be worth at least 1 credit. Even small subjects deserve credit! ⭐',
            'credits.max' => 'Subject credits seem high. Most subjects are 6 credits or fewer! 📊',
        ];
    }
}