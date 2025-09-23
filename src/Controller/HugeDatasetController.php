<?php
declare(strict_types=1);

namespace App\Controller;

use App\Enum\CacheStatus;
use App\Service\HugeDatasetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HugeDatasetController extends AbstractController
{
    public function __construct(
        private readonly HugeDatasetService $hugeDatasetService
    ) {}

    #[Route('/process-huge-dataset', name: 'process_huge_dataset', methods: ['GET'])]
    public function processHugeDataset(): JsonResponse
    {
        $result = $this->hugeDatasetService->getHugeDataset();

        return $this->json(
            $result->data,
            $result->status === CacheStatus::WARMING ? 202 : 200,
            ['X-Cache-Status' => $result->status->value],
        );
    }
}
