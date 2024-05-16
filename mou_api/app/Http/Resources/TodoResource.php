<?php

namespace App\Http\Resources;

use App\Enums\TodoType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* dd($this); */
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'creator' => UserResource::make($this->creator),
            'users' => ContactResource::collection($this->contacts),
            'done_time' => $this->done_time,
            'overline' => (bool) $this->overline,
            'parent_Id' => (int) $this->parent_id,
            'children' => empty($this->type) && $this->type == TodoType::SINGLE ? null : TodoResource::collection($this->childrenNotDone),
            'updated_by' => $this->children->count() ? $this->updated_by : $this->creator?->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at_api,
        ];
    }
}
