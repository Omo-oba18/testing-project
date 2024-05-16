<?php

namespace App\Http\Requests;

use App\Rules\EmployeeBelongsToCompanyRule;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => ['bail', 'required', 'string', 'max:255'],
            'description' => ['bail', 'nullable', 'string', 'max:255'],
            'client' => ['bail', 'required', 'string', 'max:255'],
            'employee_responsible_id' => ['bail', 'required', 'integer', new EmployeeBelongsToCompanyRule($this->route('company_id'))],
            'teams' => ['nullable', 'array'],
            'teams.*' => ['bail', 'required', 'integer', new EmployeeBelongsToCompanyRule($this->route('company_id'))],
            'tasks' => ['bail', 'required', 'array'],
            'tasks.*.title' => ['bail', 'required', 'string', 'max:255'],
            'tasks.*.comment' => ['bail', 'nullable', 'string', 'max:255'],
            'tasks.*.employees' => ['bail', 'required', 'array'],
            'tasks.*.employees.*' => ['bail', 'required', 'integer', new EmployeeBelongsToCompanyRule($this->route('company_id'))],
            'tasks.*.start_date' => ['bail', 'required', 'date_format:Y-m-d', 'after:'.date('Y-m-d', strtotime('-1 days'))],
            'tasks.*.end_date' => ['bail', 'nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];

        $projectID = $this->route('project_id');
        // Apply for edit project case
        if (! empty($projectID)) {
            // Project's task edit will progress in other routes
            unset($rules['tasks']);
        }

        return $rules;
    }
}
