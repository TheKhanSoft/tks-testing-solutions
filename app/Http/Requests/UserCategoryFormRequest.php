<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCategoryFormRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:user_categories,name,' . $this->user_category, // Unique except for the current user category being updated
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
            'name.required' => 'User category name is needed! What kind of users are these? 🤔',
            'name.unique' => 'User category name is already taken.  Let\'s be more original! ✨',
            'name.max' => 'User category name is too long.  Keep it concise! 🤏',
            'description.string' => 'User category description should be text.  Describe this user group! ✍️',
        ];
    }
}