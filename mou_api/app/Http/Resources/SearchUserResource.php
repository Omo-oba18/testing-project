<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchUserResource extends JsonResource
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
            //            "email"=> $this->email,
            //            "birthday"=> $this->birthday,
            'full_address' => $this->fullAddress,
            //            "phone_number"=> $this->phone_number,
            //            "dial_code"=> $this->dial_code,
            'avatar' => User::getPhotoAsset($this->avatar),
        ];
    }
}
