<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ride
 */
class RideResource extends JsonResource
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
            'name' => $this->name,
            'legs' => $this->printable_legs,
            'pre_margin' => $this->pre_margin,
            'post_margin' => $this->post_margin,
            'start' => $this->start->toDateTimeString(),
            'end' => $this->end->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
