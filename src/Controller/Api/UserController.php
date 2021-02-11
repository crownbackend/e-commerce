<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Api\RegistrationApiType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/users")
 * Class UserController
 * @package App\Controller\Api
 */
class UserController extends AbstractController
{
    private $userRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder,
                                Mailer $mailer)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
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
     * @Route("/", name="create_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function createUser(Request $request): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(RegistrationApiType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $this->generatePasswordRandom(10)));
            $user->setPasswordToken($this->generateToken());
            $user->setConfirmToken($this->generateToken());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->mailer->sendEmail($user->getEmail(), $user->getConfirmToken(), 'registration');
            $this->mailer->sendEmail($user->getEmail(), $user->getPasswordToken(), 'forget-password');
            return $this->json(['created' => 1], 201);
        }
        return $this->json($form->getErrors(), 400);
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
    private function generatePasswordRandom($nb_car, $chaine = 'azertyuiopqsdfghjklmwxcvbn123456789')
    {
        $nb_lettres = strlen($chaine) - 1;
        $generation = '';
        for($i=0; $i < $nb_car; $i++)
        {
            $pos = mt_rand(0, $nb_lettres);
            $car = $chaine[$pos];
            $generation .= $car;
        }
        return $generation;
    }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}