<?php
declare(strict_types=1);

namespace App\Provider;

interface DatasetProviderInterface
{
    public function getDataset(): array;
}
