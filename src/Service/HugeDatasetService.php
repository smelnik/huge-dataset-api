<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\CacheResult;
use App\Enum\CacheStatus;
use App\Provider\DatasetProviderInterface;
use Psr\Log\LoggerInterface;
use Redis;

final readonly class HugeDatasetService
{
    private const CACHE_KEY = 'huge_dataset';
    private const CACHE_EXPIRE_TTL = 60;
    private const LOCK_KEY = 'huge_dataset_lock';
    private const LOCK_EXPIRE_TTL = 15;

    public function __construct(
        private Redis $redis,
        private CacheLock $cacheLock,
        private LoggerInterface $logger,
        private DatasetProviderInterface $datasetProvider,
    ) {}

    public function getHugeDataset(): CacheResult
    {
        $this->logger->debug('Processing huge dataset');

        $cachedRaw = $this->redis->get(self::CACHE_KEY);
        $cached = $cachedRaw ? json_decode($cachedRaw, true) : null;
        $isFresh = $cached && isset($cached['updated_at']) && (time() - $cached['updated_at'] < self::CACHE_EXPIRE_TTL);

        if ($isFresh) {
            return $this->result(CacheStatus::HIT, $cached['data']);
        }

        $lock = $this->cacheLock->acquire(self::LOCK_KEY, self::LOCK_EXPIRE_TTL);

        if (!$lock) {
            if ($cached) {
                return $this->result(CacheStatus::STALE, $cached['data']);
            }

            return $this->result(
                CacheStatus::WARMING,
                ["message" => "The dataset is being processed and cached. Please retry later."]
            );
        }

        try {
            sleep(10);

            $data = $this->datasetProvider->getDataset();

            $this->redis->set(self::CACHE_KEY, json_encode(['data' => $data, 'updated_at' => time()]));
        } finally {
            $lock->release();
        }

        return $this->result(CacheStatus::MISS, $data);
    }

    private function result(CacheStatus $cacheStatus, ?array $data = null): CacheResult
    {
        $this->logger->debug("Cache " . $cacheStatus->name);
        return new CacheResult($cacheStatus, $data);
    }
}
