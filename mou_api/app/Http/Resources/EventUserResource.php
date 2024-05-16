<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EventUserResource extends JsonResource
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
            'name' => $this->name ? $this->name : optional($this->userContact)->name,
            'avatar' => User::getPhotoAsset($this->avatar ? $this->avatar : optional($this->userContact)->avatar),
            'connectycube_id' => $this->connectycube_id ?? optional($this->userContact)->connectycube_id ?? null,
            $this->mergeWhen(isset($this->pivot) && isset($this->pivot->status), [
                'is_accepted' => isset($this->pivot->status) && $this->pivot->status == config('constant.event.status.confirm') ? true : false,
            ]),
        ];
    }
}
