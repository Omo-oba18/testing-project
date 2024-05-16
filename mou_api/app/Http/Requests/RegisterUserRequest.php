<?php

namespace App\Http\Requests;

use App\Helpers\Country;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
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

            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            //            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
            'avatar' => ['required', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.User::AVATAR_MAXSIZE],
            'birthday' => ['required', 'date_format:Y-m-d'],
            'gender' => ['required', Rule::in(config('constant.gender'))],
            'country_code' => ['required', Rule::in(array_keys(Country::getCountries()))],
            'city' => ['required', 'string', 'max:255'],
        ];
    }
}
