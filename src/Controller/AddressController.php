<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use App\Utils\BaseController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends BaseController
{

    /**
     * @OA\Post(description="New Address",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(  property="street", type="string",  example="1 Abby Road" ),
     *               @OA\Property(  property="city", type="string",  example="London" ),
     *               @OA\Property(  property="postcode", type="string",  example="W1 2PQ" ),
     *               ),
     *           )
     *       )
     *   ),
     *  )
     */
    #[Route('/v1/address', name: 'address_add', methods: 'POST')]
    public function addressAdd(AddressRepository $addressRepository, Request $request ): Response
    {
        $user = $this->getUser();
        if ($user) {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $address = new Address();
            $form = $this->createForm(AddressType::class, $address);
            $form->submit($data);
            if ($form->isSubmitted() && $form->isValid()) {
                $address->setUser($user);
                $addressRepository->add($address, true);

                $data = $this->serialize($address, ['read']);
                return $this->json($data);
            }
        }

        return $this->json('', 422);
    }


    #[Route('/v1/address', name: 'address_list', methods: 'GET')]
    public function addressList(AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();
        $addresses = $addressRepository->findBy(['user'=>$user]);

        $data = $this->serialize($addresses, ['read']);

        return $this->json($data);
    }
}