<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RosterResource extends JsonResource
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
            'employee' => new RosterEmployeeResource($this->whenLoaded('employee')),
            'creator_id' => $this->creator_id,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'store' => $this->store ? StoreResource::make($this->store) : $this->store,
            'total_deny' => $this->status == config('constant.event.status.deny') ? 1 : 0,
        ];
    }
}
