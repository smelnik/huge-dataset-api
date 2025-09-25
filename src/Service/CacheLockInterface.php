<?php
declare(strict_types=1);

namespace App\Service;

interface CacheLockInterface
{
    public function acquire(string $lockKey, int $ttl = 15): ?object;
}
