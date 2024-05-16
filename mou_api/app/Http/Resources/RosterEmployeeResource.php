<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterEmployeeResource extends JsonResource
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
            'employee_name' => $this->getEmployeeName(),
            'employee_avatar' => User::getPhotoAsset($this->getEmployeeAvatar()),
            'role_name' => $this->role_name,
        ];
    }
}
