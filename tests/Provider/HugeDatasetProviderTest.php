<?php
declare(strict_types=1);

namespace App\Tests\Provider;

use App\Provider\HugeDatasetProvider;
use PHPUnit\Framework\TestCase;

final class HugeDatasetProviderTest extends TestCase
{
    public function testDatasetStructure(): void
    {
        $provider = new HugeDatasetProvider();
        $dataset = $provider->getDataset();

        $this->assertIsArray($dataset, 'Dataset should be an array');
        $this->assertGreaterThanOrEqual(5, count($dataset), 'Dataset should contain at least 5 objects');
        foreach ($dataset as $index => $item) {
            $this->assertIsArray($item, "Item at index $index should be an array");
            $this->assertGreaterThanOrEqual(2, count($item), "Item at index $index should contain at least 2 fields");
        }

        $json = json_encode($dataset);
        $this->assertJson($json, 'Dataset should be serializable to JSON');
    }
}
