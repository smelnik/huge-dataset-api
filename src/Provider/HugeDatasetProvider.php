<?php
declare(strict_types=1);

namespace App\Provider;

use App\Dto\Person;

final readonly class HugeDatasetProvider implements DatasetProviderInterface
{
    public function getDataset(): array
    {
        return [
            new Person(1, 'Alice'),
            new Person(2, 'Bob'),
            new Person(3, 'Charlie'),
            new Person(4, 'David'),
            new Person(5, 'Eve'),
        ];
    }
}
