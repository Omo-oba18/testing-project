<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class TrimPhoneNumber extends TransformsRequest
{
    /**
     * The attributes that should be trimmed.
     *
     * @var array
     */
    protected $accept = [
        'phone_number',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->accept, true)) {
            return ltrim($value, '0');
        }

        return $value;
    }
}
