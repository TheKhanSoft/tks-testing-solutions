<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UserCategoryFormRequest extends FormRequest
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
        $categoryId = $this->route('user_category') ? $this->route('user_category')->id : $this->user_category;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:user_categories,name,{$categoryId}"
            ],
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
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
            'name.required' => 'User category needs a name! What type of user is this? 👥',
            'name.unique' => 'This user category name already exists. Time for some creative thinking! ✨',
            'name.max' => 'User category name is too long. Keep it short and sweet! 🍬',
            'description.string' => 'Description should be text. Tell us about this user category! ✍️',
            'description.max' => 'Description is too long. Keep it under 1000 characters please! 📏',
            'permissions.array' => 'Permissions should be a list of abilities. What can these users do? 🛡️',
            'permissions.*.string' => 'Each permission must be text. What powers are we granting? 🦸',
            'permissions.*.exists' => 'Oops! One of those permissions doesn\'t exist in our system. Let\'s check the list! 🧐',
        ];
    }
}
