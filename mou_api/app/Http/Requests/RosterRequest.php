<?php

namespace App\Http\Requests;

use App\Rules\EmployeeBelongsToCompanyRule;
use App\Services\AuthService;
use Illuminate\Foundation\Http\FormRequest;

class RosterRequest extends FormRequest
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
    public function rules(AuthService $authService)
    {
        $arr = [
            'start_time' => 'required|date_format:Y-m-d H:i:s', //'after:'.date('Y-m-d H:i:s')
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            'store_id' => 'required|integer',
        ];
        if (! $this->id) { // when create roster then employee is required
            $company = $authService->getCompany();
            $arr['company_employee_id'] = ['required', 'integer', new EmployeeBelongsToCompanyRule(optional($company)->id ?? 0)];
        }

        return $arr;
    }
}
