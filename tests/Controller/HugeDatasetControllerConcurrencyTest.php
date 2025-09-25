<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HugeDatasetControllerConcurrencyTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient();
    }

    private function requestDataset(): array
    {
        $this->client->request('GET', '/process-huge-dataset');

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        return [
            'statusCode'   => $response->getStatusCode(),
            'cacheStatus'  => $response->headers->get('X-Cache-Status'),
            'data'         => $data,
        ];
    }

    public function testMultipleSequentialRequests(): void
    {
        // First request: no cache -> MISS (200)
        $first = $this->requestDataset();
        $this->assertSame(200, $first['statusCode']);
        $this->assertContains($first['cacheStatus'], ['MISS', 'HIT']);
        $this->assertIsArray($first['data']);

        // Second request: HIT / STALE / WARMING
        $second = $this->requestDataset();
        $this->assertContains($second['statusCode'], [200, 202]);
        $this->assertContains($second['cacheStatus'], ['HIT', 'STALE', 'WARMING']);
        $this->assertIsArray($second['data']);

        // Third request: HIT
        $third = $this->requestDataset();
        $this->assertSame(200, $third['statusCode']);
        $this->assertSame('HIT', $third['cacheStatus']);
        $this->assertIsArray($third['data']);
    }
}
