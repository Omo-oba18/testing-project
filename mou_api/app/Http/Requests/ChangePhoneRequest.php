<?php

namespace App\Http\Requests;

use App\Helpers\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePhoneRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'max:20', 'unique:users'],
            'dial_code' => ['required', Rule::in(Country::getCountryDialCodes())],
            'email' => 'required|email|exists:users|exists:verify_emails',
            'code' => 'required|exists:verify_emails,token',
        ];
    }
}
