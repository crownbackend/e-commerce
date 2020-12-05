<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api")
 * Class SecurityController
 * @package App\Controller\Api
 *
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login_check", name="login_api", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder,
                          JWTTokenManagerInterface $JWTTokenManager, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $user = $userRepository->findOneBy(['email' => $data['username']]);
        if($user == null) {
            return $this->json(["notAccount" => 0], 200);
        }
        if($user->getEnabled() == 0) {
            return  $this->json(['enable' => 0], 200);
        }
        if($user && $passwordEncoder->isPasswordValid($user, $data['password'])) {
            if(in_array("ROLE_ADMIN",$user->getRoles())) {
                return $this->json(["token" => $JWTTokenManager->create($user), "email" => $user->getEmail()]);
            } else {
                return $this->json(["error_admin" => 0]);
            }
        } else {
            return $this->json(["errorLogin" => 0], 200);
        }
    }

    /**
     * @Route("/check/login/verify/token"), name="verify_token", methods={"GET"})
     * @param Request $request
     * @param JWTEncoderInterface $JWTEncoder
     * @return JsonResponse
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function checkToken(Request $request, JWTEncoderInterface $JWTEncoder): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $tokenValid = $JWTEncoder->decode($token);
        if($tokenValid['username']) {
            return $this->json(["token_valid" => 1], Response::HTTP_OK);
        } else {
            return $this->json(["token_not_valid" => 0], Response::HTTP_BAD_REQUEST);
        }
    }
}