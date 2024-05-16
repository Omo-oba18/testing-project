<?php

namespace App\Http\Resources;

use App\Helpers\Util;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectOfEmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $allTeam = [];
        $teams = [];
        foreach ($this->teams as $employee) {
            $teams[] = [
                'id' => $employee->id,
                'name' => $employee->getEmployeeName(),
            ];
        }
        if ($this->employeeResponsible) {
            $allTeam[] = [
                'id' => optional($this->employeeResponsible->contact)->user_contact_id,
                'name' => $this->employeeResponsible->getEmployeeName(),
                'avatar' => $this->employeeResponsible->getEmployeeAvatar() ? Util::file_url($this->employeeResponsible->getEmployeeAvatar()) : null,
                'connectycube_id' => optional(optional($this->employeeResponsible->contact)->userContact)->connectycube_id,
            ];
        }
        if ($this->teams && $this->teams->count() > 0) {
            foreach ($this->teams as $employee) {
                $allTeam[] = [
                    'id' => optional($employee->contact)->user_contact_id,
                    'name' => $employee->getEmployeeName(),
                    'avatar' => $employee->getEmployeeAvatar() ? Util::file_url($employee->getEmployeeAvatar()) : null,
                    'connectycube_id' => optional(optional($employee->contact)->userContact)->connectycube_id,
                ];
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'user' => $allTeam,
            'room_chat_id' => $this->room_chat_id,
            'company_photo' => $this?->company?->logo_url,
            'description' => $this->description,
            'client' => $this->client,
            'company_name' => optional($this->company)->name,
            'employee_responsible' => [
                'id' => $this->employee_responsible_id,
                'name' => $this->employeeResponsible ? $this->employeeResponsible->getEmployeeName() : '',
            ],
            'teams' => $teams,
            'creator_id' => $this->creator_id,
            'tasks' => ProjectTaskResource::collection($this->tasks),
        ];
    }
}
