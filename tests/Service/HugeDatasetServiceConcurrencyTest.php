<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Enum\CacheStatus;
use App\Service\CacheLockInterface;
use App\Service\HugeDatasetService;
use App\Provider\DatasetProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Redis;

final class HugeDatasetServiceConcurrencyTest extends TestCase
{
    private const SAMPLE_DATA = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
    ];

    private function createService(
        array $cachedData = null,
        array $lockReturns = [true],
    ): HugeDatasetService {
        $redis = $this->createMock(Redis::class);

        // Cache content
        $redis->method('get')->willReturn($cachedData ? json_encode($cachedData) : false);
        $redis->method('set')->willReturn(true);
        $redis->method('del')->willReturn(1);

        $logger = $this->createMock(LoggerInterface::class);

        $provider = $this->createMock(DatasetProviderInterface::class);
        $provider->method('getDataset')->willReturn(self::SAMPLE_DATA);

        $cacheLock = $this->createMock(CacheLockInterface::class);

        // Simulate concurrent lock acquisition
        $lockObjects = [];
        foreach ($lockReturns as $acquired) {
            if ($acquired) {
                $lockObjects[] = new class { public function release(): void {} };
            } else {
                $lockObjects[] = null;
            }
        }
        $cacheLock->method('acquire')
            ->willReturnOnConsecutiveCalls(...$lockObjects);

        return new HugeDatasetService($redis, $cacheLock, $logger, $provider);
    }

    public function testCacheEmptyFirstRequest(): void
    {
        // First request acquires lock, second doesn't
        $service = $this->createService(
            null, // no cache
            [true, false] // first acquires, second fails
        );

        $first = $service->getHugeDataset();
        $second = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::MISS, $first->status);
        $this->assertEquals(self::SAMPLE_DATA, $first->data);

        $this->assertEquals(CacheStatus::WARMING, $second->status);
        $this->assertArrayHasKey('message', $second->data);
    }

    public function testCacheStaleDuringUpdate(): void
    {
        $staleCache = ['data' => self::SAMPLE_DATA, 'updated_at' => time() - 120];

        $service = $this->createService(
            $staleCache,
            [true, false] // first acquires, second fails
        );

        $first = $service->getHugeDataset();
        $second = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::MISS, $first->status);
        $this->assertEquals(self::SAMPLE_DATA, $first->data);

        $this->assertEquals(CacheStatus::STALE, $second->status);
        $this->assertEquals(self::SAMPLE_DATA, $second->data);
    }

    public function testCacheHitAfterUpdate(): void
    {
        $freshCache = ['data' => self::SAMPLE_DATA, 'updated_at' => time()];

        $service = $this->createService(
            $freshCache,
            [false, false] // nobody acquires lock
        );

        $first = $service->getHugeDataset();
        $second = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::HIT, $first->status);
        $this->assertEquals(self::SAMPLE_DATA, $first->data);

        $this->assertEquals(CacheStatus::HIT, $second->status);
        $this->assertEquals(self::SAMPLE_DATA, $second->data);
    }
}
