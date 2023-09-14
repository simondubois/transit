<?php

namespace App\Enums;

enum RideSyncStatus: string
{
    case Idle = 'idle';

    /**
     * Get the emoji.
     */
    public function emoji(): string
    {
        $emojis = [
            RideSyncStatus::Idle->value => 'ℹ️',
        ];

        return $emojis[$this->value];
    }

    /**
     * Get the name.
     */
    public function name(): string
    {
        $names = [
            RideSyncStatus::Idle->value => 'Väntar på synkronisering',
        ];

        return $names[$this->value];
    }

    /**
     * Get the variant.
     */
    public function variant(): string
    {
        $variants = [
            RideSyncStatus::Idle->value => 'info',
        ];

        return $variants[$this->value];
    }
}
