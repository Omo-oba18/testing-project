<?php

namespace App\Http\Requests;

use App\Company;
use App\Helpers\Country;
use App\Services\AuthService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCompanyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(AuthService $authService)
    {
        $routeName = $this->route()->getName();
        $company = $authService->getCompany();
        // Update logo
        if ($routeName == 'api.company.update_logo') {
            return [
                'logo' => ['required', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.Company::AVATAR_MAXSIZE],
            ];
        }
        // Create company
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:companies'],
            'logo' => ['required', 'image', 'mimes:jpeg,bmp,png,jpg,gif', 'max:'.Company::AVATAR_MAXSIZE],
            'country_code' => ['required', Rule::in(array_keys(Country::getCountries()))],
            'city' => ['required', 'string', 'max:255'],
        ];
        // Update profile
        if ($routeName == 'api.company.update_profile') {
            unset($rules['logo']);
            $rules['address'] = ['nullable', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:companies,email,'.$company->id];
        }

        return $rules;
    }
}
