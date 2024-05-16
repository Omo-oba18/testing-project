<?php

namespace App\Http\Requests;

use App\Enums\BusinessType;
use App\Enums\StatusEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessAndPersonalRequest extends FormRequest
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
            'status' => ['bail', 'required', Rule::in(StatusEvent::getValues())],
            'type' => ['bail', 'nullable', 'array'],
            'type.*' => ['bail', 'required',  Rule::in(BusinessType::getValues())],
        ];
    }
}
