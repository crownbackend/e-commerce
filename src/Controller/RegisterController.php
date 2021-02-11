<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordForgetType;
use App\Form\RegistrationApiType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder,
                                Mailer $mailer, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
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
        $form = $this->createForm(RegistrationApiType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            $user->setConfirmToken($this->generateToken());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->mailer->sendEmail($user->getEmail(), $user->getConfirmToken(), 'registration');
            $this->addFlash("success", "register.success");
            return $this->redirectToRoute('app_login');
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

    /**
     * @Route("/paswword/forget", name="password_forget")
     * @param Request $request
     * @return Response
     */
    public function passwordForget(Request $request): Response
    {
        $form = $this->createForm(PasswordForgetType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['email' => $form->get('password')->getData()]);
            if($user) {
                $user->setPasswordToken($this->generateToken());
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->mailer->sendEmail($user->getEmail(), $user->getPasswordToken(), 'forget-password');
                $this->addFlash('success', 'forget_password.success');
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('error', 'forget_password.error');
            }
        }
        return $this->render('registration/password-forget.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/account/password/confirm/{token}", name="confirm_password")
     * @param string $token
     */
    public function confirmPassword(string $token, Request $request)
    {
        $user = $this->userRepository->findOneBy(['passwordToken' => $token]);
        if(!$user) {
            return $this->redirectToRoute('home');
        }
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user->setPasswordToken(null);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'forget_password.reset.success');
            return $this->redirectToRoute('app_login');
        }

        return $this->render("registration/reset-password.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/check/email/validate", name="check_email")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        if($request->isXmlHttpRequest()) {
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
        } else {
            return $this->json(['error' => 'not found']);
        }
    }

    /**
     * @Route("/check/password/validate", name="check_password")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkPassword(Request $request): JsonResponse
    {
        if($request->isXmlHttpRequest()) {
            $pattern = '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,50}$';
            if(preg_match("/$pattern/", $request->request->get('password'))) {
                return $this->json(['success' => 1, "message" => $this->translator->trans('register.errors.password.success')]);
            } else {
                return $this->json(['error' => 0, "message" => $this->translator->trans('register.errors.password.error')]);
            }
        } else {
            return $this->json(['error' => 'not found']);
        }
    }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}