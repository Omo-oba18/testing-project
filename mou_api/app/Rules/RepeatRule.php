<?php

namespace App\Rules;

use App\Event;
use Illuminate\Contracts\Validation\Rule;

class RepeatRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! empty($value)) {
            $arRepeat = explode(Event::CHARACTER_SPECIAL, $value);
            if (count($arRepeat) > 0) {
                $error = false;
                foreach ($arRepeat as $r) {
                    if (! in_array($r, config('constant.event.repeat'))) {
                        $error = true;
                        break;
                    }
                }

                return ! $error;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_in');
    }
}
