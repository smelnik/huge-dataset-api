<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class CacheLock
{
    public function __construct(private LockFactory $lockFactory) {}

    public function acquire(string $lockKey, int $ttl = 15): ?LockInterface
    {
        $lock = $this->lockFactory->createLock($lockKey, $ttl);

        if ($lock->acquire()) {
            return $lock;
        }

        return null;
    }
}
