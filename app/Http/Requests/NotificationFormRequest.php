<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationFormRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'nullable|string|max:50|in:info,success,warning,error,default',
            'is_read' => 'sometimes|boolean',
            'link' => 'nullable|string|max:2000|url',
            'data' => 'nullable|json',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
            'expires_at' => 'nullable|date|after:scheduled_at',
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
            'user_id' => 'user',
            'is_read' => 'read status',
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
            'user_id.required' => 'User ID is needed! Who should receive this notification? 👤',
            'user_id.exists' => 'That user doesn\'t exist in our system. Check the user ID! 🔍',
            'title.required' => 'Notification needs a title! What\'s this about? 📢',
            'title.max' => 'Title is too long. Keep it under 255 characters! 📏',
            'message.required' => 'Message is essential! What do you want to tell the user? 💬',
            'message.max' => 'Message is too long. Keep it under 5000 characters! 📝',
            'type.string' => 'Notification type should be text. Is it an alert, update, or reminder? 🏷️',
            'type.max' => 'Type is too lengthy. 50 characters maximum! ✂️',
            'type.in' => 'Invalid notification type. Must be info, success, warning, error, or default. 🎭',
            'is_read.boolean' => 'Read status must be true or false. Has this been read yet? 👁️',
            'link.max' => 'Link is too long. URLs have limits too! 🔗',
            'link.url' => 'Link must be a valid URL format (e.g., https://example.com). 🌐',
            'data.json' => 'Additional data must be in JSON format. Check your format! 📋',
            'scheduled_at.date' => 'Scheduled time must be a valid date and time. When should this be sent? 🕒',
            'scheduled_at.after_or_equal' => 'Scheduled time cannot be in the past. Choose a present or future time! ⏰',
            'expires_at.date' => 'Expiry time must be a valid date and time. When should this expire? 📆',
            'expires_at.after' => 'Expiry time must be after the scheduled time. That\'s just logical! ⏳',
        ];
    }
}
