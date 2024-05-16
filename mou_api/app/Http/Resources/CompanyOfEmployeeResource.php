<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyOfEmployeeResource extends JsonResource
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
            'id' => $this->company->id,
            'name' => $this->company->name,
            'logo' => User::getPhotoAsset($this->company->logo),
            'role_name' => $this->role_name,
            'confirmed' => $this->employee_confirm == 'Y' ? true : false,
        ];
    }
}
