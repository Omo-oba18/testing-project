<?php

namespace App\Http\Requests;

use App\Rules\EmployeeBelongsToCompanyRule;
use App\Rules\RepeatRule;
use App\Rules\StoreRule;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['bail', 'required', 'string', 'max:255'],
            'start_date' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:'.date('Y-m-d')],
            'end_date' => ['bail', 'nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'comment' => ['bail', 'nullable', 'string', 'max:255'],
            'employees' => ['bail', 'required', 'array'],
            'employees.*' => [
                'bail',
                'required',
                'integer',
                new EmployeeBelongsToCompanyRule($this->route('company_id')),
            ],
            'repeat' => [
                'bail',
                'nullable',
                'string',
                new RepeatRule(),
            ],
            'store_id' => ['bail', 'required', 'integer', new StoreRule($this->route('company_id'))],
        ];
    }
}
