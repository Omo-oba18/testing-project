<?php

namespace App\Http\Requests;

use App\Enums\TodoType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TodoRequest extends FormRequest
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
            'title' => 'bail|required|string|max:255',
            'type' => ['bail', $this->parent_id ? 'nullable' : 'required', Rule::in(TodoType::getValues())],
            'contact_ids' => 'bail|nullable|array',
            'contact_ids.*' => 'bail|nullable|exists:contacts,id',
            'parent_id' => ['bail', 'nullable', 'numeric', $this->parent_id ? Rule::exists('todos', 'id')->where('type', TodoType::GROUP) : null],
        ];
    }
}
