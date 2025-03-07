<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaperCategoryFormRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:paper_categories,name,' . $this->paper_category, // Unique except for the current paper category being updated
            'description' => 'nullable|string',
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
            'name.required' => 'Paper category name is required! What kind of paper is this? 🤔',
            'name.unique' => 'Paper category name already exists.  Let\'s think of a unique category! ✨',
            'name.max' => 'Paper category name is too long. Shorten it, please! ✂️',
            'description.string' => 'Paper category description should be text.  Tell us about this category! ✍️',
        ];
    }
}