<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $teams = [];
        foreach ($this->teams as $employee) {
            $teams[] = [
                'id' => $employee->id,
                'name' => $employee->getEmployeeName(),
            ];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'client' => $this->client,
            'company_name' => optional($this->company)->name,
            'company_logo' => $this?->company?->logo_url,
            'employee_responsible' => [
                'id' => $this->employee_responsible_id,
                'name' => $this->employeeResponsible ? $this->employeeResponsible->getEmployeeName() : '',
            ],
            'teams' => $teams,
            'creator_id' => $this->creator_id,
            'tasks' => ProjectTaskResource::collection($this->tasks),
            'type' => $this->type,
            'total_deny' => $this->contact_denies_count ?? null,
        ];
    }
}
