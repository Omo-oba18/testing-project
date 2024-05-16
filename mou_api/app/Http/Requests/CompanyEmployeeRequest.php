<?php

namespace App\Http\Requests;

use App\Services\AuthService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyEmployeeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(AuthService $authService)
    {
        $user = $authService->getUserAuth();
        $rules = [
            'contact_id' => [
                'required',
                Rule::exists('contacts', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'role_name' => ['required', 'string', 'max:255'],
            'permission_access_business' => ['required', 'boolean'],
            'permission_add_task' => ['required', 'boolean', function ($attribute, $value, $fail) {
                if (! $this->request->get('permission_access_business') && $value) {
                    $fail(__('validation.you_must_allow_permission', ['action' => __('validation.add_task')]));
                }
            }],
            'permission_add_project' => ['required', 'boolean', function ($attribute, $value, $fail) {
                if (! $this->request->get('permission_access_business') && $value) {
                    $fail(__('validation.you_must_allow_permission', ['action' => __('validation.add_project')]));
                }
            }],
            'permission_add_employee' => ['required', 'boolean', function ($attribute, $value, $fail) {
                if (! $this->request->get('permission_access_business') && $value) {
                    $fail(__('validation.you_must_allow_permission', ['action' => __('validation.add_employee')]));
                }
            }],
            'permission_add_roster' => ['required', 'boolean', function ($attribute, $value, $fail) {
                if (! $this->request->get('permission_access_business') && $value) {
                    $fail(__('validation.you_must_allow_permission', ['action' => __('validation.add_roster')]));
                }
            }],
        ];

        $employeeID = $this->route('employee_id');
        // Apply for edit employee case
        if (! empty($employeeID)) {
            unset($rules['contact_id']);
        }

        return $rules;
    }
}
