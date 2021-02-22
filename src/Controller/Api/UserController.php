<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Api\RegistrationType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder,
                                Mailer $mailer, TranslatorInterface $translator, HttpClientInterface $client)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->client = $client;
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
    public function create(Request $request): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $this->generatePasswordRandom(10)));
            $user->setPasswordToken($this->generateToken());
            $user->setConfirmToken($this->generateToken());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->mailer->sendEmail($user->getEmail(), $user->getConfirmToken(), 'registration', $this->translator->trans('confirm_email.subject'));
            $this->mailer->sendEmail($user->getEmail(), $user->getPasswordToken(), 'forget-password', $this->translator->trans('forget_password.subject'));
            return $this->json(['created' => 1], 201);
        }
        return $this->json($form->getErrors(), 400);
    }

    /**
     * @Route("/user/{id}/edit", name="edit_user", methods={"PUT"})
     * @param $id
     * @return JsonResponse
     */
    public function edit($id, Request $request, ValidatorInterface $validator): JsonResponse
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
     * @Route("/user/{id}/delete", name="delete_user", methods={"DELETE"})
     */
    public function delete(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->json([], 204);
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

    /**
     * @Route("/check/email/validate", name="check_email_validate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if($user) {
            return $this->json(['taken' => 1, 'message' => $this->translator->trans('register.errors.email.taken')]);
        } else {
            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json(['success' => 1, 'message' => $this->translator->trans('register.errors.email.success')]);
            }
            return $this->json(['error' => 0, 'message' => $this->translator->trans('register.errors.email.error')]);
        }
    }

    /**
     * @Route("/check/address/{address}", name="check_address", methods={"GET"})
     * @return JsonResponse
     */
    public function checkAddress(string $address): JsonResponse
    {
        if($address) {
            $response = $this->client->request(
                'GET',
                $this->getParameter("api_address_gouv").$address
            );

            $data = json_decode($response->getContent(), true);

            return $this->json($data);
        } else {
            return $this->json("not address get");
        }

    }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
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
}