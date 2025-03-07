<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password; // Import Password rule

class FacultyMemberFormRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:faculty_members,email,' . $this->faculty_member, // Unique except for the current faculty member being updated
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable', // Required on create, optional on update
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => 'sometimes|required_with:password', // Only required if password is provided
            'department_id' => 'nullable|exists:departments,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example image validation
            'designation' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean', // For toggling active status
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
            'name.required' => 'Faculty member needs a name! What should we call them? 👤',
            'name.max' => 'Faculty member name is too long. Let\'s shorten it a bit! 🤏',
            'email.required' => 'Email is essential! How will they get important updates? 📧',
            'email.email' => 'Please enter a valid email format.  Is there an "@" and a "." in there? 🤔',
            'email.unique' => 'This email is already registered. Is this faculty member secretly already here? 🕵️',
            'email.max' => 'Email is too long. Emails are usually shorter, right? 😉',
            'password.required' => 'Password is a must for security! Let\'s set a strong one. 🔒',
            'password.confirmed' => 'Passwords don\'t match! Double-check those fingers. 👯‍♀️',
            'password.min' => 'Password needs to be at least 8 characters. Make it strong! 💪',
            'password.mixed_case' => 'Password should include both uppercase and lowercase letters. Mix it up! AaBbCc',
            'password.numbers' => 'Password needs at least one number. Numbers are your friends: 123!',
            'password.symbols' => 'Password needs at least one symbol. Symbols add spice: !@#$%',
            'password_confirmation.required_with' => 'Please confirm your password! Just to be sure. ✅',
            'department_id.exists' => 'Selected department is invalid.  Let\'s pick a department from the list. 🏢',
            'profile_picture.image' => 'Profile picture must be an image file. Show us your best shot! 📸',
            'profile_picture.mimes' => 'Profile picture must be in JPEG, PNG, JPG, or GIF format.  Choose a common image type.🖼️',
            'profile_picture.max' => 'Profile picture is too big! Max size is 2MB. Let\'s slim it down. 🏋️',
            'designation.string' => 'Designation should be text. What\'s their title? 🎓',
            'designation.max' => 'Designation is too long. Keep it concise, like a business card. 💼',
            'is_active.boolean' => 'Active status must be either true or false. Are they in or out? 🚪',
        ];
    }
}