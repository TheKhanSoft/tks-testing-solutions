<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class StudentFormRequest extends FormRequest
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
        // Format name properly
        if ($this->name) {
            $this->merge([
                'name' => Str::title($this->name),
            ]);
        }
        
        // Normalize phone number if provided
        if ($this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $this->phone),
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
        $studentId = $this->route('student') ? $this->route('student')->id : $this->student;
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                "unique:students,email,{$studentId}"
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
            'student_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                "unique:students,student_id,{$studentId}"
            ],
            'department_id' => 'required|exists:departments,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
            'batch' => 'nullable|string|max:50',
            'semester' => 'nullable|string|max:50',
            'session' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'address' => 'nullable|string|max:500',
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
            'name.required' => 'Student name is required! Who are we enrolling today? 👩‍🎓👨‍🎓',
            'name.max' => 'Student name is too long. Let\'s keep it to 255 characters! 📏',
            'email.required' => 'Email address is essential! How will we send important updates? 📧',
            'email.email' => 'That doesn\'t look like a valid email. Did you forget the @ symbol? 🤔',
            'email.unique' => 'This email is already registered. Is this student already in our system? 🔍',
            'email.max' => 'Email is too long. Most emails are shorter than this! ✂️',
            'password.required' => 'Password is needed! Security is important for student accounts. 🔒',
            'password.confirmed' => 'Passwords don\'t match! Please type it again to confirm. 🔄',
            'password.min' => 'Password should be at least 8 characters. Make it secure! 💪',
            'password.mixed_case' => 'Password needs both UPPERCASE and lowercase letters. Mix it up! AaBbCc',
            'password.numbers' => 'Add some numbers to your password. They add extra security: 123!',
            'password.symbols' => 'Throw in a symbol or two for a stronger password: !@#$%',
            'password.uncompromised' => 'This password appears in a data breach. Please choose a different one for security. 🚨',
            'password_confirmation.required_with' => 'Please confirm your password! Type it once more. ✌️',
            'student_id.required' => 'Student ID is required! Every student needs their unique identifier. 🏷️',
            'student_id.unique' => 'This Student ID is already taken. Each student needs a unique ID! 🔢',
            'student_id.max' => 'Student ID is too long. Keep it under 50 characters. 📏',
            'student_id.regex' => 'Student ID can only contain letters, numbers, hyphens, and underscores. No special characters! 🔤',
            'department_id.required' => 'Department is required! Which department will this student join? 🏛️',
            'department_id.exists' => 'Selected department doesn\'t exist. Choose from the list! 📋',
            'profile_picture.image' => 'Profile picture must be an image file. Let\'s see that smiling face! 😊',
            'profile_picture.mimes' => 'Profile picture must be JPEG, PNG, JPG, or GIF. Standard formats only! 🖼️',
            'profile_picture.max' => 'Profile picture is too large! Keep it under 2MB. 🏋️‍♀️',
            'profile_picture.dimensions' => 'Profile picture must be at least 100x100 pixels. Too small to see clearly! 🔍',
            'batch.max' => 'Batch information is too long. Shorten it to 50 characters or less. ✂️',
            'semester.max' => 'Semester info is too lengthy. 50 characters is the limit! 📝',
            'session.max' => 'Session is too detailed. 100 characters maximum, please! 📚',
            'phone.max' => 'Phone number is too long. Most phone numbers are shorter than this! 📞',
            'phone.regex' => 'Phone number can only contain digits, spaces, and these symbols: + - ( )',
            'address.max' => 'Address is too lengthy. 500 characters should be enough! 🏠',
            'is_active.boolean' => 'Active status must be true or false. Is this student active now? ✅❌',
        ];
    }
}
