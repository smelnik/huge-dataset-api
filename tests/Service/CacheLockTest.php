<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CacheLock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class CacheLockTest extends TestCase
{
    public function testAcquireSuccess(): void
    {
        $lockMock = $this->createMock(SharedLockInterface::class);
        $lockMock->method('acquire')->willReturn(true);

        $factoryMock = $this->createMock(LockFactory::class);
        $factoryMock->method('createLock')->willReturn($lockMock);

        $cacheLock = new CacheLock($factoryMock);

        $lock = $cacheLock->acquire('test_key', 10);
        $this->assertSame($lockMock, $lock);
    }

    public function testAcquireFailure(): void
    {
        $lockMock = $this->createMock(SharedLockInterface::class);
        $lockMock->method('acquire')->willReturn(false);

        $factoryMock = $this->createMock(LockFactory::class);
        $factoryMock->method('createLock')->willReturn($lockMock);

        $cacheLock = new CacheLock($factoryMock);

        $lock = $cacheLock->acquire('test_key', 10);
        $this->assertNull($lock);
    }
}
