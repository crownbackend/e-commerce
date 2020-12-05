<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/users")
 * Class UserController
 * @package App\Controller\Api
 */
class UserController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/{filter}", name="users", methods={"GET"}, defaults={"filter": 1})
     * @return JsonResponse
     */
    public function users($filter = null): JsonResponse
    {
        $users = $this->userRepository->findByUsers('ROLE_USER', $filter);
        $usersCount = $this->userRepository->findByUserCount("ROLE_USER");
        return $this->json(["users" => $users, "usersCount" => $usersCount[1]], 200, [], ['groups' => "users"]);
    }

    /**
     * @Route("/user/{id}", name="user", methods={"GET"})
     * @return JsonResponse
     */
    public function user($id): JsonResponse
    {
        return $this->json($this->userRepository->findOneBy(['id' => $id]), 200, [], ['groups' => "user"]);
    }

    /**
     * @Route("/user/{id}/edit", name="edit_user", methods={"PUT"})
     * @param $id
     * @return JsonResponse
     */
    public function editUser($id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $user = $this->userRepository->findOneBy(["id" => $id]);
        $user->setLastName($request->request->get('lastName'));
        $user->setFirstName($request->request->get('firstName'));
        $user->setEnabled($request->request->get('enabled'));
        $user->setEmail($request->request->get('email'));
        $user->setAddress($request->request->get('address'));
        $user->setCity($request->request->get('city'));
        $user->setTelephone($request->request->get('telephone'));
        // Validate data with validator
        $errors = $validator->validate($user, null, 'user');
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse($errorsString);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->json(['id' => $user->getId()]);
    }

    /**
     * @Route("/load/more/{filter}/{date}", name="users_more", methods={"GET"})
     * @return JsonResponse
     */
    public function loadUsers($filter, $date): JsonResponse
    {
        $dateF = new \DateTime($date);
        $users = $this->userRepository->findByUsersByLoadMore($filter, $dateF->format('Y-m-d H:i:s'),
            "ROLE_USER");
        return $this->json($users, 200, [], ['groups' => "users"]);
    }

    /**
     * @Route("/search/user", name="user_search", methods={"POST"})
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $users = $this->userRepository->findSearchByUser($request->request->get('search'));
        return $this->json($users, 200, [], ['groups' => "users"]);
    }
}