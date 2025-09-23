<?php
declare(strict_types=1);

namespace App\Enum;

enum CacheStatus: string
{
    case HIT = 'HIT';
    case MISS = 'MISS';
    case STALE = 'STALE';
    case WARMING = 'WARMING';
}
