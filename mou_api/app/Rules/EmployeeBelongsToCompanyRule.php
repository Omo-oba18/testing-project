<?php

namespace App\Rules;

use App\CompanyEmployee;
use Illuminate\Contracts\Validation\Rule;

class EmployeeBelongsToCompanyRule implements Rule
{
    private $companyID;

    /**
     * Create a new rule instance.
     */
    public function __construct(int $companyID)
    {
        $this->companyID = $companyID;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        return CompanyEmployee::where('company_id', $this->companyID)->where('id', trim($value))->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.exists');
    }
}
