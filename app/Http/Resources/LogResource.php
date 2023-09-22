<?php

namespace App\Http\Resources;

use App\Models\Account;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Log
 */
class LogResource extends JsonResource
{
    /**
     * Transform the holder into an array.
     */
    protected function holderToArray(): ?JsonResource
    {
        if ($this->holder instanceof Account) {
            return new AccountResource($this->holder);
        }

        if ($this->holder instanceof Calendar) {
            return new CalendarResource($this->holder);
        }

        return null;
    }

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
            'holder' => $this->holderToArray(),
            'job_type' => $this->job_type,
            'job_started_at' => $this->job_started_at->toDateTimeString(),
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
