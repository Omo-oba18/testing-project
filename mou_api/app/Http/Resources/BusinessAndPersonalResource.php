<?php

namespace App\Http\Resources;

use App\Enums\BusinessType;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessAndPersonalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $contacts = [];
        switch ($this->type) {
            case BusinessType::PROJECT:
                foreach ($this->teams as $value) {
                    $contacts[] = $value->contact;
                }
                break;
            case BusinessType::TASK:
                $contacts = $this->contacts;
                break;
            case BusinessType::PROJECT_TASK:
                $contacts = $this->contacts;
                break;
            case BusinessType::ROSTER:
                $contacts[] = $this->employee?->contact;
                break;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'company_photo' => $this->type == BusinessType::ROSTER ? $this->creator?->company?->logo_url : $this?->company?->logo_url,
            'company_name' => $this->type == BusinessType::ROSTER ? $this->creator?->company?->name : $this?->company?->name,
            'creator' => UserResource::make($this->creator),
            $this->mergeWhen(! empty($this->type), [
                'type' => $this->type,
            ]),
            'users' => ContactResource::collection($contacts),
            'project_name' => $this->project ? $this->project->title : null,
            'project_id' => $this->project ? $this->project->id : null,
            'project_start_date' => $this->project ? $this->project->start_date : null,
            'project_end_date' => $this->project ? $this->project->end_date : null,
            'store_name' => $this->store?->name,
            'scope_name' => optional($this->project)->description,
            'leader_name' => optional($this->project)->employeeResponsible?->getEmployeeName(),
            'client_name' => optional($this->project)->client,
            'comment' => $this->comment,
            'show_end_date' => isset($this->show_end_date) ? (bool) $this->show_end_date : true,
        ];
    }
}
