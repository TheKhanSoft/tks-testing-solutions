<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestAttemptFormRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'paper_id' => 'required|exists:papers,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time', // End time should be after start time
            'score' => 'nullable|integer',
            'status' => 'nullable|string|max:255', // Validate status values if needed
            'is_stopped' => 'sometimes|boolean',
            'browser_info' => 'nullable|string',
            'ip_address' => 'nullable|ip',
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
            'user_id.required' => 'User ID is required! Who is taking this test? 👤',
            'user_id.exists' => 'Invalid user ID. Make sure it\'s a registered user! 🧐',
            'paper_id.required' => 'Paper ID is needed! Which paper is being attempted? 📝',
            'paper_id.exists' => 'Invalid paper ID.  Choose a paper from the available list! 🧐',
            'start_time.date' => 'Start time must be a valid date and time. When did it begin? ⏱️',
            'end_time.date' => 'End time must be a valid date and time. When did it finish? ⏱️',
            'end_time.after' => 'End time must be after the start time. Time flows forward! ➡️',
            'score.integer' => 'Score must be a number. What\'s the result? 🔢',
            'status.string' => 'Status should be text.  What\'s the attempt status?  🚦',
            'status.max' => 'Status is too long. Keep it brief, like a label! 🏷️',
            'is_stopped.boolean' => 'Stopped status must be true or false. Is it stopped by admin? 🛑 or ▶️?',
            'browser_info.string' => 'Browser info should be text.  What browser did they use? 🌐',
            'ip_address.ip' => 'IP address must be a valid IP address.  Where are they connecting from? 🌍',
        ];
    }
}