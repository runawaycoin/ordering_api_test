<?php

namespace App\Controller;

use App\Security\ApiAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: 'POST')]
    public function login(ApiAuthenticator $authenticator, Request $request): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $firewallName = 'main';
        //$token =  (new UsernamePasswordToken($user, $firewallName, $user->getRoles()))->;
        $token = 'TOK' . $user->getUserIdentifier();

       return $this->json([
             'user'  => $user->getUserIdentifier(),
           'token' => $token,
       ]);
    }
}
