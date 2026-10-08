<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Open = 'open';
    case Full = 'full';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Deschis',
            self::Full => 'Complet',
            self::Finished => 'Încheiat',
            self::Cancelled => 'Anulat',
        };
    }
}
