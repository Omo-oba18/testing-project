<?php

namespace App\Http\Requests;

use App\Event;
use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:'.date('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:start_date'],
            'comment' => ['nullable', 'string', 'max:255'],
            'repeat' => [
                'nullable',
                function ($attribute, $value, $fail) {
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
                            if ($error) {
                                $fail(trans('validation.not_in', ['attribute' => $attribute]));
                            }
                        }
                    }
                },
            ],
            'alarm' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $arRepeat = explode(Event::CHARACTER_SPECIAL, $value);
                        if (count($arRepeat) > 0) {
                            $error = false;
                            foreach ($arRepeat as $r) {
                                if (! in_array(trim($r), config('constant.event.alarm'))) {
                                    $error = true;
                                    break;
                                }
                            }
                            if ($error) {
                                $fail(trans('validation.not_in', ['attribute' => $attribute]));
                            }
                        }
                    }
                },
            ],
            'place' => ['nullable', 'string', 'max:255'],
            'busy_mode' => ['nullable', 'boolean'],
            'chat' => ['nullable', 'boolean'],

            'users' => ['nullable', 'array'],
            'users.*' => ['required', 'integer'],
            'store_id' => ['nullable', 'integer'],
        ];
    }
}
