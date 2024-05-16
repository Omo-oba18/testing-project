<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'name' => $this->name ? $this->name : optional($this->userContact)->name,
            'email' => optional($this->userContact)->email,
            'birthday' => optional($this->userContact)->birthday,
            'full_address' => optional($this->userContact)->fullAddress,
            'country_code' => optional($this->userContact)->country_code,
            'city' => optional($this->userContact)->city,
            'phone_number' => $this->phone_number ? $this->phone_number : optional($this->userContact)->phone_number,
            'dial_code' => $this->dial_code ? $this->dial_code : optional($this->userContact)->dial_code,
            'avatar' => User::getPhotoAsset($this->avatar ? $this->avatar : optional($this->userContact)->avatar),
            'connectycube_id' => optional($this->userContact)->connectycube_id,
            'user_contact_id' => intval($this->user_contact_id),
            $this->mergeWhen(isset($this->pivot) && isset($this->pivot->status), [
                'is_accepted' => isset($this->pivot->status) && $this->pivot->status == config('constant.event.status.confirm') ? true : false,
            ]),
        ];
    }
}
