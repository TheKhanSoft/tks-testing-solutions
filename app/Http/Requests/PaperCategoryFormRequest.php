<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PaperCategoryFormRequest extends FormRequest
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
        $paperCategoryId = $this->route('paper_category') ? $this->route('paper_category')->id : $this->paper_category;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                "unique:paper_categories,name,{$paperCategoryId}"
            ],
            'description' => 'nullable|string|max:1000',
            'color_code' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
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
            'name.required' => 'Paper category name is required! What kind of paper is this? 🤔',
            'name.unique' => 'Paper category name already exists. Let\'s think of a unique category! ✨',
            'name.max' => 'Paper category name is too long. Shorten it, please! ✂️',
            'description.string' => 'Paper category description should be text. Tell us about this category! ✍️',
            'description.max' => 'Description is too long. Keep it under 1000 characters! 📚',
            'color_code.max' => 'Color code is too long. It should be a hex color code like #RRGGBB! 🎨',
            'color_code.regex' => 'Invalid color format. Use hexadecimal format like #RRGGBB! 🎨',
            'icon.max' => 'Icon name is too long. Keep it under 50 characters! 🔣',
        ];
    }
}