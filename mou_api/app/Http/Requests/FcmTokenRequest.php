<?php

namespace App\Http\Requests;

use App\UserDeviceFcm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FcmTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'device' => ['required', Rule::in(UserDeviceFcm::DEVICES)],
        ];
    }
}
