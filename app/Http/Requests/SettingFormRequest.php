<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SettingFormRequest extends FormRequest
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
        if ($this->key) {
            $this->merge([
                'key' => Str::slug($this->key, '_'),
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
        $settingId = $this->route('setting') ? $this->route('setting')->id : $this->setting;
        
        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_\.]+$/',
                "unique:settings,key,{$settingId}"
            ],
            'value' => 'nullable',
            'type' => 'nullable|string|max:50|in:string,integer,float,boolean,array,object,null',
            'group' => 'nullable|string|max:100|regex:/^[a-z0-9_\.]+$/',
            'is_public' => 'sometimes|boolean',
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
            'key.required' => 'Setting key is essential! What setting are we configuring? 🔧',
            'key.unique' => 'This setting key already exists. Each setting needs a unique identifier! 🔑',
            'key.max' => 'Setting key is too long. Keep it under 255 characters! 📏',
            'key.regex' => 'Setting key should only contain lowercase letters, numbers, dots and underscores. No spaces or special characters! 🔤',
            'type.string' => 'Setting type should be text. What kind of setting is this? 🏷️',
            'type.max' => 'Setting type is too lengthy. 50 characters or less, please! ✂️',
            'type.in' => 'Invalid setting type. Must be one of: string, integer, float, boolean, array, object, or null. 📋',
            'group.string' => 'Setting group should be text. How should we categorize this setting? 📁',
            'group.max' => 'Setting group name is too long. Keep it under 100 characters! 📝',
            'group.regex' => 'Setting group should only contain lowercase letters, numbers, dots and underscores. 🔤',
            'is_public.boolean' => 'Public status must be true or false. Should everyone see this setting? 👀',
        ];
    }
}
