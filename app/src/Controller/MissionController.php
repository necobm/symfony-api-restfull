<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Exception\DenormalizationException;
use App\Exception\InvalidFormatException;
use App\Service\MissionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/mission', name: 'mission_')]
class MissionController
{
    public function __construct(
        private MissionService $missionService
    ){}

    #[Route(null, name: 'read_all', methods: ["GET"])]
    public function readAll(): JsonResponse
    {
        /** @var Mission[] $missions */
        $missions = $this->missionService->getAll();
        return new JsonResponse($this->missionService->transformObjectCollectionToArray($missions));
    }

    #[Route('/{id}', name: 'read', methods: ["GET"])]
    public function read(int $id): JsonResponse
    {
        /** @var Mission $mission */
        $mission = $this->missionService->getOne(resourcesId: $id);
        return is_null($mission)
            ? new JsonResponse(null, Response::HTTP_NOT_FOUND)
            : new JsonResponse($this->missionService->transformObjectToArray($mission));
    }

    /**
     * @throws InvalidFormatException|DenormalizationException
     */
    #[Route(null, name: 'create', methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $missionData = json_encode($request->toArray(), true);

        if($missionData === false){
            return new JsonResponse([
                'error' => "The Request Payload has an invalid format"
            ],
            Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Mission $mission */
        $mission = $this->missionService->createObjectFromJson($missionData);

        return new JsonResponse(
            $this->missionService->transformObjectToArray($mission),
            Response::HTTP_CREATED
        );
    }

    /**
     * @throws InvalidFormatException|DenormalizationException
     */
    #[Route('/{id}', name: 'update', methods: ["PUT", "PATCH"])]
    public function update(int $id, Request $request): JsonResponse
    {
        $mission = $this->missionService->getOne(resourcesId: $id);

        if (is_null($mission)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $missionData = json_encode($request->toArray(), true);

        $mission = $this->missionService->updateObjectFromJson($missionData, $mission);

        return new JsonResponse(
            $this->missionService->transformObjectToArray($mission),
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $mission = $this->missionService->getOne(resourcesId: $id);

        if (is_null($mission)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try{
            $this->missionService->remove($mission);
        }
        catch (AccessDeniedException $exception){
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}