<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Event
 */
class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'calendar_id' => $this->calendar_id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'incoming_ride_sync_status' => $this->incoming_ride_sync_status,
            'outgoing_ride_sync_status' => $this->outgoing_ride_sync_status,
            'start' => $this->start->toDateTimeString(),
            'end' => $this->end->toDateTimeString(),
        ];
    }
}
