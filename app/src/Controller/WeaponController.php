<?php

namespace App\Controller;

use App\Entity\Weapon;
use App\Exception\DenormalizationException;
use App\Exception\InvalidFormatException;
use App\Service\WeaponService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/weapon', name: 'weapon_')]
class WeaponController
{
    public function __construct(
        private WeaponService $weaponService
    ){}

    #[Route(null, name: 'read_all', methods: ["GET"])]
    public function readAll(): JsonResponse
    {
        /** @var Weapon[] $weapons */
        $weapons = $this->weaponService->getAll();
        return new JsonResponse($this->weaponService->transformObjectCollectionToArray($weapons));
    }

    #[Route('/{id}', name: 'read', methods: ["GET"])]
    public function read(int $id): JsonResponse
    {
        /** @var Weapon $weapon */
        $weapon = $this->weaponService->getOne(resourcesId: $id);
        return is_null($weapon)
            ? new JsonResponse(null, Response::HTTP_NOT_FOUND)
            : new JsonResponse($this->weaponService->transformObjectToArray($weapon));
    }

    /**
     * @throws DenormalizationException|InvalidFormatException
     */
    #[Route(null, name: 'create', methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $weaponData = json_encode($request->toArray(), true);

        if($weaponData === false){
            return new JsonResponse([
                'error' => "The Request Payload has an invalid format"
            ],
            Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Weapon $weapon */
        $weapon = $this->weaponService->createObjectFromJson($weaponData);

        return new JsonResponse(
            $this->weaponService->transformObjectToArray($weapon),
            Response::HTTP_CREATED
        );
    }

    /**
     * @throws InvalidFormatException|DenormalizationException
     */
    #[Route('/{id}', name: 'update', methods: ["PUT", "PATCH"])]
    public function update(int $id, Request $request): JsonResponse
    {
        $weapon = $this->weaponService->getOne(resourcesId: $id);

        if (is_null($weapon)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $weaponData = json_encode($request->toArray(), true);

        $weapon = $this->weaponService->updateObjectFromJson($weaponData, $weapon);

        return new JsonResponse(
            $this->weaponService->transformObjectToArray($weapon),
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $weapon = $this->weaponService->getOne(resourcesId: $id);

        if (is_null($weapon)) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try{
            $this->weaponService->remove($weapon);
        }
        catch (AccessDeniedException $exception){
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}