<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyEmployeeResource extends JsonResource
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
            'contact' => $this->contact_id ? [
                'id' => $this->contact_id,
                'name' => $this->getEmployeeName(),
                'avatar' => User::getPhotoAsset($this->getEmployeeAvatar()),
            ] : null,
            'role_name' => $this->role_name,
            'permission_access_business' => (int) $this->permission_access_business,
            'permission_add_task' => (int) $this->permission_add_task,
            'permission_add_project' => (int) $this->permission_add_project,
            'permission_add_employee' => (int) $this->permission_add_employee,
            'permission_add_roster' => (int) $this->permission_add_roster,
            'company_id' => (int) $this->company_id,
            'employee_confirm' => $this->employee_confirm,
        ];
    }
}
