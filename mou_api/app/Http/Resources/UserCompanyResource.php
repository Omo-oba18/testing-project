<?php

namespace App\Http\Resources;

use App\Services\AuthService;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $authService = new AuthService();
        // Get company belong to user auth
        $company = $authService->getCompany();

        // Get permission
        $permissionCompany = null;
        if ($company) {
            $permissionCompany = $authService->getPermissionCompany($company->id);
        }

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
                //                'busy_mode'     => optional($this->setting)->busy_mode ?? 0,
                'language_code' => optional($this->setting)->language_code ?? config('constant.languages.english'),
            ],
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'address' => $company->address,
                'country_code' => $company->country_code,
                'city' => $company->city,
                'logo' => User::getPhotoAsset($company->logo),
                'working_days' => $company->working_days,
            ] : null,
            'permission' => [
                'is_creator' => $company ? $company->creator_id == $this->id : false,
                'permission_access_business' => $permissionCompany ? (bool) $permissionCompany->permission_access_business : false,
                'permission_add_task' => $permissionCompany ? (bool) $permissionCompany->permission_add_task : false,
                'permission_add_project' => $permissionCompany ? (bool) $permissionCompany->permission_add_project : false,
                'permission_add_employee' => $permissionCompany ? (bool) $permissionCompany->permission_add_employee : false,
            ],
        ];
    }
}
