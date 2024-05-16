<?php

namespace App\Http\Resources;

use App\Company;
use App\Enums\EventType;
use App\Roster;
use App\Services\AuthService;
use Illuminate\Http\Resources\Json\JsonResource;

class EventAndRosterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $arr = [
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
            'users' => [],
            'done_time' => $this->done_time,
            'room_chat_id' => $this->room_chat_id,
            'company_photo' => null,
            'project_name' => optional($this->project)->title,
            'project_id' => optional($this->project)->id,
            'project_start_date' => optional($this->project)->start_date,
            'project_end_date' => optional($this->project)->end_date,
            'scope_name' => optional($this->project)->description,
            'leader_name' => optional($this->project)->employeeResponsible?->getEmployeeName(),
            'client_name' => optional($this->project)->client,
            'show_end_date' => isset($this->show_end_date) ? (bool) $this->show_end_date : true,
        ];

        if ($this->type != EventType::ROSTER->value) { // event, etc
            $authService = new AuthService();
            $user = $authService->getUserAuth();

            $waitingToConfirm = false;
            if ($this->type == EventType::EVENT->value && $this->creator_id === $user->id) {

                $waitingToConfirm = $this->start_date < now()->format('Y-m-d H:i') ? false : true;
            } else {
                $waitingToConfirm = $this->contacts->contains(function ($tag) use ($user) {
                    return $tag->pivot?->status === config('constant.event.status.waiting') && $tag->userContact?->id == $user->id;
                });
            }

            $denied = $this->contacts->contains(function ($contact) {
                return $contact->pivot->status === config('constant.event.status.deny');
            });
            $company_photo = optional($this->company)->logo;
            $arr['waiting_to_confirm'] = $waitingToConfirm;
            $arr['company_photo'] = $company_photo ? Company::getPhotoAsset($company_photo) : null;
            $arr['company_name'] = optional($this->company)->name ?? null;
            $arr['status'] = $denied ?
                config('constant.event.status.deny') : $this->getProjectTaskStatus();
            $arr['users'] = EventUserResource::collection($this->contacts);
            $arr['store_name'] = optional($this->store)->name ?? null;
        } else { // format roster
            $roster = Roster::with('employee.contact')->find($this->id);
            $company_photo = optional($this->creator->company)->logo;
            $arr['company_photo'] = $company_photo ? Company::getPhotoAsset($company_photo) : null;
            $arr['waiting_to_confirm'] = $this->status === config('constant.event.status.waiting');
            $arr['status'] = $this->status;
            $arr['company_name'] = $this->company_name;
            $arr['users'] = [EventUserResource::make($roster->employee->contact)];
            $arr['store_name'] = optional($this->store)->name ?? null;
        }

        return $arr;
    }
}
