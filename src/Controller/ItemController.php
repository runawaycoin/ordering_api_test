<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use App\Utils\BaseController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends BaseController
{

    /**
     * @OA\Post(description="Login",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(  property="name", type="string",  example="box" ),
     *               @OA\Property( property="price", type="number", example=5
     *               ),
     *           )
     *       )
     *   ),
     *  )
     */
    #[Route('/v1/admin/item', name: 'item_add', methods: 'POST')]
    public function itemAdd(ItemRepository $itemRepository, Request $request ): Response
    {
        $user = $this->getUser();
        if ($user) {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $item = new Item();
            $form = $this->createForm(ItemType::class, $item);
            $form->submit($data);
            if ($form->isSubmitted() && $form->isValid()) {
                $itemRepository->add($item, true);

                $data = $this->serialize($item, ['read']);
                return $this->json($data);
            }
        }

        return $this->json('', 422);
    }

    #[Route('/v1/item', name: 'items', methods: 'GET')]
    public function items(ItemRepository $itemRepository): Response
    {

        $items = $itemRepository->findAll();

        $data = $this->serialize($items);

        return $this->json($data);
    }
}