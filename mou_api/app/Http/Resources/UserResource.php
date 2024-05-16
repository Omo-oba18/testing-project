<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'gender' => $this->gender != null ? (int) $this->gender : null,
            'full_address' => $this->fullAddress,
            'country_code' => $this->country_code,
            'city' => $this->city,
            'phone_number' => $this->phone_number,
            'dial_code' => $this->dial_code,
            'avatar' => User::getPhotoAsset($this->avatar),
            'settings' => [
                'busy_mode' => optional($this->setting)->busy_mode ?? 0,
                'language_code' => optional($this->setting)->language_code ?? config('constant.languages.english'),
            ],
            'connectycube_id' => $this->connectycube_id,
        ];
    }
}
