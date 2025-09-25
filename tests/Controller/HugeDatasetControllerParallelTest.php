<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HugeDatasetControllerParallelTest extends WebTestCase
{
    public function testConcurrentClients(): void
    {
        self::ensureKernelShutdown();
        $client1 = self::createClient();

        self::ensureKernelShutdown();
        $client2 = self::createClient();

        $client1->request('GET', '/process-huge-dataset');
        $response1 = $client1->getResponse();

        $this->assertContains($response1->getStatusCode(), [200, 202]);
        $this->assertTrue($response1->headers->has('X-Cache-Status'));
        $this->assertContains(
            $response1->headers->get('X-Cache-Status'),
            ['MISS', 'HIT']
        );

        $client2->request('GET', '/process-huge-dataset');
        $response2 = $client2->getResponse();

        $this->assertContains($response2->getStatusCode(), [200, 202]);
        $this->assertTrue($response2->headers->has('X-Cache-Status'));
        $this->assertContains(
            $response2->headers->get('X-Cache-Status'),
            ['HIT', 'STALE', 'WARMING']
        );

        if ($response2->headers->get('X-Cache-Status') === 'WARMING') {
            $this->assertSame(202, $response2->getStatusCode());
        }
    }
}
