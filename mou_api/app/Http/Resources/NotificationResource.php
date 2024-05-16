<?php

namespace App\Http\Resources;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->data['action'] ?? '',
            'title' => $this->data['title'] ?? '',
            'body' => $this->data['body'] ?? '',
            'type' => $this->data['user_type'] ?? '',
            'read_at' => $this->read_at,
            'time_ago' => $this->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_TO_NOW, true),
            'avatar' => $this->data['avatar'] ?? '',
            'route_name' => $this->data['route_name'] ?? null,
            'arguments' => isset($this->data['arguments']) ? strval($this->data['arguments']) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
