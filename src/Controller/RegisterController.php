<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/{_locale}")
 * Class RegisterController
 * @package App\Controller
 */
class RegisterController extends AbstractController
{
    /**
     * @var UserRepository
     */
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder,
                                Mailer $mailer, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            $user->setConfirmToken($this->generateToken());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->mailer->sendEmail($user->getEmail(), $user->getConfirmToken());
            $this->addFlash("success", "Inscription réussie !");
        }
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/account/confirm/{token}", name="confirm_account")
     * @param string $token
     * @return RedirectResponse
     */
    public function confirmAccount(string $token): RedirectResponse
    {
        $user = $this->userRepository->findOneBy(['confirmToken'=> $token]);
        if($user) {
            $user->setConfirmToken(null);
            $user->setEnabled(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'confirm_email.success');
            return $this->redirectToRoute('redirect_locale');
        } else {
            $this->addFlash('error', 'confirm_email.error');
            return $this->redirectToRoute('redirect_locale');
        }
    }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}