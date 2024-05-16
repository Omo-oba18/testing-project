<?php

namespace App\Http\Resources;

use App\Services\AuthService;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
        $user = $authService->getUserAuth();

        // Check Event - wating to confirm.
        $waitingToConfirm = false;
        // Is Event
        if ($this->isEvent()) {
            if ($this->creator_id === $user->id && $this->contacts->count() > 0) {
                foreach ($this->contacts as $uTag) {
                    if ($uTag->pivot->status === config('constant.event.status.waiting')) {
                        $waitingToConfirm = true;
                        break;
                    }
                }
            }
        }
        $company_photo = optional($this->company)->logo;

        return [
            'type' => $this->type,
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'comment' => $this->comment,
            'repeat' => $this->repeat,
            'alarm' => $this->alarm,
            'place' => $this->place,
            'chat' => $this->chat != null ? (int) $this->chat : null,
            'busy_mode' => $this->busy_mode != null ? (int) $this->busy_mode : null,
            'creator' => new EventUserResource($this->creator),
            'users' => EventUserResource::collection($this->contacts),
            'waiting_to_confirm' => $waitingToConfirm,
            'project_name' => optional($this->project)->title,
            'project_id' => optional($this->project)->id,
            'project_start_date' => optional($this->project)->start_date,
            'project_end_date' => optional($this->project)->end_date,
            'done_time' => $this->done_time,
            'room_chat_id' => $this->room_chat_id,
            'status' => $this->type == config('constant.event.type.project_task') ? $this->getProjectTaskStatus() : null,
            'company_photo' => $company_photo ? \Storage::url($company_photo) : null,
            'store' => $this->store ? StoreResource::make($this->store) : $this->store,
            'store_name' => $this->store ? $this->store->name : null,
            'scope_name' => optional($this->project)->description,
            'leader_name' => optional($this->project)->employeeResponsible?->getEmployeeName(),
            'client_name' => optional($this->project)->client,
            'show_end_date' => (bool) $this->show_end_date,
        ];
    }
}
