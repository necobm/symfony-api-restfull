<?php

namespace App\Controller;

use App\Service\FactionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

#[Route('/faction', name: 'faction_')]
class FactionController
{
    public function __construct(
        private FactionService $factionService
    ){}

    #[Route('/', name: 'read_all', methods: ["GET"])]
    public function readAll(): JsonResponse
    {
        $factions = $this->factionService->getAllFactions();
        return new JsonResponse($factions);
    }

    #[Route('/{id}', name: 'read', methods: ["GET"])]
    public function read(int $id): JsonResponse
    {
        $faction = $this->factionService->getFaction(factionId: $id);
        return is_null($faction)
            ? new JsonResponse(
            null,
            Response::HTTP_NOT_FOUND
        )   : new JsonResponse(
            $this->factionService->transformFactionToArray($faction)
        );
    }

    #[Route('/', name: 'create', methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $factionData = json_encode($request->toArray(), true);

        if($factionData === false){
            return new JsonResponse([
                'error' => "The Request Payload has an invalid format"
            ],
            Response::HTTP_BAD_REQUEST
            );
        }

        $faction = $this->factionService->createFactionFromJson($factionData);

        return new JsonResponse(
            $this->factionService->transformFactionToArray($faction),
            Response::HTTP_CREATED
        );
    }
}