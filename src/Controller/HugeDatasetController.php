<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\Person;
use App\Enum\CacheStatus;
use App\Service\HugeDatasetService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HugeDatasetController extends AbstractController
{
    public function __construct(
        private readonly HugeDatasetService $hugeDatasetService
    ) {}

    #[OA\Get(
        path: '/process-huge-dataset',
        description: 'Generates or returns a cached huge dataset',
        summary: 'Process huge dataset',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cache STALE, HIT or MISS',
                headers: [
                    new OA\Header(
                        header: 'X-Cache-Status', // <-- обязательно
                        description: 'Cache status',
                        schema: new OA\Schema(type: 'string', enum: ['HIT','MISS','STALE','WARMING'])
                    )
                ],
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Person::class))
                )
            ),
            new OA\Response(
                response: 202,
                description: 'Cache is warming',
                headers: [
                    new OA\Header(
                        header: 'X-Cache-Status', // <-- обязательно
                        description: 'Cache status',
                        schema: new OA\Schema(type: 'string', enum: ['HIT','MISS','STALE','WARMING'])
                    )
                ],
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
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
