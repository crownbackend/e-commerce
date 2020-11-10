<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer {
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**op
     * @param $email
     * @param $token
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail($email, $token, $template)
    {
        $email = (new TemplatedEmail())
            ->from('resgister@shop.com')
            ->to(new Address($email))
            ->subject($this->translator->trans("confirm_email.subject"))

            // path of the Twig template to render
            ->htmlTemplate('emails/'.$template.'.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'token' => $token,
            ])
        ;

        $this->mailer->send($email);
    }
}