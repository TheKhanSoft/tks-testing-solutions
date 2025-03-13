<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttemptFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'paper_id' => 'required|exists:papers,id',
            'student_id' => 'required|exists:students,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'total_score' => 'nullable|numeric|min:0',
            'status' => [
                'required',
                'string',
                'in:not_started,in_progress,completed,submitted,graded,expired'
            ],
            'submission_data' => 'nullable|json',
            'grading_notes' => 'nullable|string|max:2000',
            'graded_by' => 'nullable|exists:users,id',
            'graded_at' => 'nullable|date|after_or_equal:end_time',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:500',
            'is_proctored' => 'sometimes|boolean',
            'attempt_number' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    protected function attributes(): array
    {
        return [
            'paper_id' => 'paper',
            'student_id' => 'student',
            'graded_by' => 'grader',
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
            'paper_id.required' => 'Paper ID is needed! Which test is being attempted? 📄',
            'paper_id.exists' => 'That paper doesn\'t exist in our system. Let\'s choose a real test! 🔍',
            'student_id.required' => 'Student ID is essential! Who\'s taking this test? 👨‍🎓',
            'student_id.exists' => 'We can\'t find this student in our system. Is the ID correct? 🤔',
            'start_time.date' => 'Start time needs to be a valid date and time. When did this attempt begin? ⏱️',
            'end_time.date' => 'End time must be a valid date and time. When did this attempt finish? ⏰',
            'end_time.after_or_equal' => 'End time can\'t be before start time. That would be time travel! ⏳',
            'total_score.numeric' => 'Total score must be a number. How many points did the student earn? 💯',
            'total_score.min' => 'Total score can\'t be negative. Even zero is better than negative! 🙂',
            'status.required' => 'Status is needed! What\'s the current state of this attempt? 🚦',
            'status.in' => 'Invalid status. Must be not_started, in_progress, completed, submitted, graded, or expired. 📊',
            'submission_data.json' => 'Submission data must be in JSON format. Check the format! 📋',
            'grading_notes.string' => 'Grading notes should be text. Any feedback for the student? ✍️',
            'grading_notes.max' => 'Grading notes are too long. Keep them under 2000 characters! 📚',
            'graded_by.exists' => 'The grader ID doesn\'t match any user in our system. Who graded this? 👩‍🏫',
            'graded_at.date' => 'Grading time must be a valid date and time. When was this graded? 🕒',
            'graded_at.after_or_equal' => 'Grading time can\'t be before the end time. Logical sequence please! ⏭️',
            'ip_address.ip' => 'Please provide a valid IP address. Format like 192.168.1.1 or 2001:db8::1 🌐',
            'user_agent.max' => 'User agent string is too long. 500 characters maximum! 💻',
            'is_proctored.boolean' => 'Proctoring status must be true or false. Was this test monitored? 👁️',
            'attempt_number.integer' => 'Attempt number must be a whole number. Which try is this? 🔢',
            'attempt_number.min' => 'Attempt number must be at least 1. Can\'t have a zero attempt! 1️⃣',
        ];
    }
}
