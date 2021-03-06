<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLoginController extends AbstractController
{

    /**
     * Login with email / password over api
     * @OA\Post(description="Login",
     *   @OA\RequestBody(  required=true,
     *       @OA\MediaType(mediaType="application/json",
     *           @OA\Schema( type="object",
     *               @OA\Property(  property="email", type="string",  example="alan@digial.co.uk" ),
     *               @OA\Property( property="password", type="string", example="alanjeeves"
     *               ),
     *           )
     *       )
     *   ),
     *  )
     */
    #[Route('/v1/login', name: 'api_login', methods: 'POST')]
    public function login(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // should generate token from auth server
        $token = 'TOK' . $user->getUserIdentifier();

       return $this->json([
             'email'  => $user->getUserIdentifier(),
           'token' => $token,
           'id' => $user->getId()
       ]);
    }
}
