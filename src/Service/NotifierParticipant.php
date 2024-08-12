<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AllowDynamicProperties]
class NotifierParticipant
{
    public function __construct(ParticipantRepository $participantRepository, MailerInterface $mailer, Environment $twig)
    {
        $this->participantRepository = $participantRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function alerterParEmail(Sortie $sortie)
    {
        $emailsParticipants = $this->participantRepository->findMails();
        $addresses = array_map(function($email) {
            return new Address($email);
        }, $emailsParticipants);

        // Générez le contenu HTML avec Twig
        $htmlContent = $this->twig->render('emails/email_template.html.twig', [
            'sortie' => $sortie
        ]);

        $email = (new Email())
            ->from('no-reply@sortir-eni.com')
            ->bcc(...$addresses)
            ->subject('Nouvelle sortie publiée')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}