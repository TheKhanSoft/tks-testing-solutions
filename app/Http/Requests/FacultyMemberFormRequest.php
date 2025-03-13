<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class FacultyMemberFormRequest extends FormRequest
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
        
        if ($this->designation) {
            $this->merge([
                'designation' => Str::title($this->designation),
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
        $facultyMemberId = $this->route('faculty_member') ? $this->route('faculty_member')->id : $this->faculty_member;
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                "unique:faculty_members,email,{$facultyMemberId}"
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
            'department_id' => 'nullable|exists:departments,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
            'designation' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:50|unique:faculty_members,employee_id,' . $facultyMemberId,
            'phone' => 'nullable|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'bio' => 'nullable|string|max:2000',
            'specialization' => 'nullable|string|max:500',
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
            'name.required' => 'Faculty member needs a name! What should we call them? 👤',
            'name.max' => 'Faculty member name is too long. Let\'s shorten it a bit! 🤏',
            'email.required' => 'Email is essential! How will they get important updates? 📧',
            'email.email' => 'Please enter a valid email format. Is there an "@" and a "." in there? 🤔',
            'email.unique' => 'This email is already registered. Is this faculty member secretly already here? 🕵️',
            'email.max' => 'Email is too long. Emails are usually shorter, right? 😉',
            'password.required' => 'Password is a must for security! Let\'s set a strong one. 🔒',
            'password.confirmed' => 'Passwords don\'t match! Double-check those fingers. 👯‍♀️',
            'password.min' => 'Password needs to be at least 8 characters. Make it strong! 💪',
            'password.mixed_case' => 'Password should include both uppercase and lowercase letters. Mix it up! AaBbCc',
            'password.numbers' => 'Password needs at least one number. Numbers are your friends: 123!',
            'password.symbols' => 'Password needs at least one symbol. Symbols add spice: !@#$%',
            'password.uncompromised' => 'This password appears in a data breach. Please choose a safer password. 🚨',
            'password_confirmation.required_with' => 'Please confirm your password! Just to be sure. ✅',
            'department_id.exists' => 'Selected department is invalid. Let\'s pick a department from the list. 🏢',
            'profile_picture.image' => 'Profile picture must be an image file. Show us your best shot! 📸',
            'profile_picture.mimes' => 'Profile picture must be in JPEG, PNG, JPG, or GIF format. Choose a common image type.🖼️',
            'profile_picture.max' => 'Profile picture is too big! Max size is 2MB. Let\'s slim it down. 🏋️',
            'profile_picture.dimensions' => 'Profile picture must be at least 100x100 pixels. Too small to see clearly! 🔍',
            'designation.string' => 'Designation should be text. What\'s their title? 🎓',
            'designation.max' => 'Designation is too long. Keep it concise, like a business card. 💼',
            'employee_id.max' => 'Employee ID is too long. Keep it under 50 characters! 🔢',
            'employee_id.unique' => 'This Employee ID is already taken. Each faculty member needs a unique ID! 🔢',
            'phone.max' => 'Phone number is too long. Most phone numbers are shorter than this! 📞',
            'phone.regex' => 'Phone number can only contain digits, spaces, and these symbols: + - ( )',
            'bio.max' => 'Bio is too long. Keep it under 2000 characters! 📚',
            'specialization.max' => 'Specialization is too detailed. Keep it under 500 characters! 🔬',
            'is_active.boolean' => 'Active status must be either true or false. Are they in or out? 🚪',
        ];
    }
}