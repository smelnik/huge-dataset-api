<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HugeDatasetControllerTest extends WebTestCase
{
    public function testCacheHitOrMiss(): void
    {
        $client = self::createClient();
        $client->request('GET', '/process-huge-dataset');

        $response = $client->getResponse();
        $this->assertContains($response->getStatusCode(), [200, 202]);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);

        $this->assertTrue($response->headers->has('X-Cache-Status'));
        $this->assertContains(
            $response->headers->get('X-Cache-Status'),
            ['HIT','MISS','STALE','WARMING']
        );
    }
}
