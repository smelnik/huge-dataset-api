<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final readonly class CacheLock implements CacheLockInterface
{
    public function __construct(private LockFactory $lockFactory) {}

    public function acquire(string $lockKey, int $ttl = 15): ?SharedLockInterface
    {
        $lock = $this->lockFactory->createLock($lockKey, $ttl);

        if ($lock->acquire()) {
            return $lock;
        }

        return null;
    }
}
