<?php
declare(strict_types=1);

namespace App\Dto;

use App\Enum\CacheStatus;

final readonly class CacheResult
{
    public function __construct(
        public CacheStatus $status,
        public ?array $data = null,
    ) {}
}
