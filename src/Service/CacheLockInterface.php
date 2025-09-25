<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Lock\SharedLockInterface;

interface CacheLockInterface
{
    public function acquire(string $lockKey, int $ttl = 15): ?SharedLockInterface;
}
