<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\HugeDatasetService;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Process\Process;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HugeDatasetControllerConcurrencyTest extends WebTestCase
{
    private array $curlParams = ['curl', '-s', '-i', 'http://nginx/process-huge-dataset'];

    private function clearCache(): void
    {
        $redis = RedisAdapter::createConnection($_ENV['REDIS_URL']);
        $redis->flushDB();
    }

    public function testCacheLifecycle(): void
    {
        $this->clearCache();

        $p1 = new Process($this->curlParams);
        $p1->start();

        sleep(1);

        $p2 = new Process($this->curlParams);
        $p2->mustRun();
        $resp2 = $p2->getOutput();
        $this->assertStringContainsString('HTTP/1.1 202 Accepted', $resp2);
        $this->assertStringContainsString('X-Cache-Status: WARMING', $resp2);

        $p1->wait();
        $resp1 = $p1->getOutput();
        $this->assertStringContainsString('HTTP/1.1 200', $resp1);
        $this->assertStringContainsString('X-Cache-Status: MISS', $resp1);

        $p3 = new Process($this->curlParams);
        $p3->mustRun();
        $resp3 = $p3->getOutput();
        $this->assertStringContainsString('HTTP/1.1 200', $resp3);
        $this->assertStringContainsString('X-Cache-Status: HIT', $resp3);

        sleep(HugeDatasetService::CACHE_EXPIRE_TTL + 2);

        $p4 = new Process($this->curlParams);
        $p4->start();

        sleep(1);

        $p5 = new Process($this->curlParams);
        $p5->mustRun();
        $resp5 = $p5->getOutput();
        $this->assertStringContainsString('HTTP/1.1 200', $resp5);
        $this->assertStringContainsString('X-Cache-Status: STALE', $resp5);

        $p4->wait();
        $resp4 = $p4->getOutput();
        $this->assertStringContainsString('HTTP/1.1 200', $resp4);
        $this->assertStringContainsString('X-Cache-Status: MISS', $resp4);

        $p6 = new Process($this->curlParams);
        $p6->mustRun();
        $resp6 = $p6->getOutput();
        $this->assertStringContainsString('HTTP/1.1 200', $resp6);
        $this->assertStringContainsString('X-Cache-Status: HIT', $resp6);
    }
}
