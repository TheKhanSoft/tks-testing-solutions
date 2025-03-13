<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UserFormRequest extends FormRequest
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
        $userId = $this->route('user') ? $this->route('user')->id : $this->user;
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                "unique:users,email,{$userId}"
            ],
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => 'required_with:password',
            'user_category_id' => 'nullable|exists:user_categories,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
            'is_active' => 'sometimes|boolean',
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
            'name.required' => 'User needs a name! What should we call this user? 👤',
            'name.max' => 'User name is too long. Let\'s shorten it a bit! 🤏',
            'email.required' => 'Email is essential for users! How will they log in and get updates? 📧',
            'email.email' => 'Please enter a valid email format. Is there an "@" and a "." in there? 🤔',
            'email.unique' => 'This email is already registered. Is this user already signed up? 🕵️',
            'email.max' => 'Email is too long. Emails are usually shorter, right? 😉',
            'password.required' => 'Password is a must for user accounts! Let\'s set a strong one. 🔒',
            'password.confirmed' => 'Passwords don\'t match! Double-check those keystrokes. 👯‍♀️',
            'password.min' => 'Password needs to be at least 8 characters. Make it secure! 💪',
            'password.mixed_case' => 'Password should include both uppercase and lowercase letters. Mix it up! AaBbCc',
            'password.numbers' => 'Password needs at least one number. Numbers add strength: 123!',
            'password.symbols' => 'Password needs at least one symbol. Symbols make it extra secure: !@#$%',
            'password.uncompromised' => 'This password appears in a data breach. Please choose a safer password. 🚨',
            'password_confirmation.required_with' => 'Please confirm your password! Just to be absolutely sure. ✅',
            'user_category_id.exists' => 'Invalid user category selected. Choose a category from the list! 🧐',
            'profile_picture.image' => 'Profile picture must be an image file. Give them a face! 📸',
            'profile_picture.mimes' => 'Profile picture must be in JPEG, PNG, JPG, or GIF format. Choose a common image type. 🖼️',
            'profile_picture.max' => 'Profile picture file size is too large! Max size is 2MB. Let\'s reduce it a bit. 🏋️',
            'profile_picture.dimensions' => 'Profile picture must be at least 100x100 pixels. Too small to see clearly! 🔍',
            'is_active.boolean' => 'Active status must be either true or false. Is this user active or inactive? 🚪',
        ];
    }
}