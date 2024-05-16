<?php

namespace App\Http\Requests;

use App\Enums\EventTab;
use App\Enums\EventType;
use App\Enums\ToDoStatus;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventFilterRequest extends FormRequest
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
            'type' => ['nullable', 'array', Rule::in(EventType::cases())],
            'status' => ['nullable', Rule::enum(ToDoStatus::class)],
            'user_type' => ['nullable', Rule::in(UserType::getValues())],
            'tab' => ['nullable', Rule::enum(EventTab::class)],
        ];
    }
}
