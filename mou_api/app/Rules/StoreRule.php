<?php

namespace App\Rules;

use App\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreRule implements ValidationRule
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
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $storeCompany = Company::whereId($this->companyID)->whereRelation('stores', 'id', $value)->first();
        if (! $storeCompany) {
            $fail('Store invalid');
        }
    }
}
