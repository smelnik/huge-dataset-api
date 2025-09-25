<?php
declare(strict_types=1);

namespace App\Provider;

final readonly class HugeDatasetProvider implements DatasetProviderInterface
{
    public function getDataset(): array
    {
        return [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
            ['id' => 4, 'name' => 'David'],
            ['id' => 5, 'name' => 'Eve'],
        ];
    }
}
