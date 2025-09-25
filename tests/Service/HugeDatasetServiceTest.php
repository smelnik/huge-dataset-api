<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Enum\CacheStatus;
use App\Provider\DatasetProviderInterface;
use App\Service\CacheLockInterface;
use App\Service\HugeDatasetService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\Lock\SharedLockInterface;

final class HugeDatasetServiceTest extends TestCase
{
    private const SAMPLE_DATA = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
    ];

    private function createService(
        ?array $cachedData = null,
        ?bool $lockAcquired = true
    ): HugeDatasetService {
        $redis = $this->createMock(Redis::class);

        $redis->method('get')->willReturn(
            $cachedData ? json_encode($cachedData) : false
        );

        $redis->method('set')->willReturn(true);
        $redis->method('del')->willReturn(1);

        $logger = $this->createMock(LoggerInterface::class);

        $provider = $this->createMock(DatasetProviderInterface::class);
        $provider->method('getDataset')->willReturn(self::SAMPLE_DATA);

        $cacheLock = $this->createMock(CacheLockInterface::class);
        if ($lockAcquired) {
            $lockMock = $this->createMock(SharedLockInterface::class);
            $lockMock->method('release');
            $cacheLock->method('acquire')->willReturn($lockMock);
        } else {
            $cacheLock->method('acquire')->willReturn(null);
        }

        return new HugeDatasetService($redis, $cacheLock, $logger, $provider);
    }

    public function testCacheMiss(): void
    {
        $service = $this->createService();
        $result = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::MISS, $result->status);
        $this->assertIsArray($result->data);
        $this->assertCount(count(self::SAMPLE_DATA), $result->data);
        $this->assertEquals(self::SAMPLE_DATA, $result->data);
    }

    public function testCacheHit(): void
    {
        $service = $this->createService(['data' => self::SAMPLE_DATA, 'updated_at' => time()]);
        $result = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::HIT, $result->status);
        $this->assertSame(self::SAMPLE_DATA, $result->data);
    }

    public function testCacheStale(): void
    {
        $service = $this->createService(
            ['data' => self::SAMPLE_DATA, 'updated_at' => time() - HugeDatasetService::CACHE_EXPIRE_TTL - 1],
            false
        );
        $result = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::STALE, $result->status);
        $this->assertSame(self::SAMPLE_DATA, $result->data);
    }

    public function testCacheWarming(): void
    {
        $service = $this->createService(null, false);
        $result = $service->getHugeDataset();

        $this->assertEquals(CacheStatus::WARMING, $result->status);
        $this->assertIsArray($result->data);
        $this->assertArrayHasKey('message', $result->data);
    }
}
