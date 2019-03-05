<?php
// /src/Mailer/NotificationMailer.php

namespace App\Mailer;

use App\Entity\Filleul;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class NotificationMailer
{
	private $mailer;
	private $templating;

	public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
	{
		$this->mailer = $mailer;
	    $this->templating = $templating;
	}

	public function sendNotification(Filleul $filleul, $object, $toEmail, $fromEmail)
	{
		$message = (new \Swift_Message($object))
                ->setFrom($fromEmail)
                ->setTo($toEmail)
                ->setBody(
                    $this->templating->render(
                        'NewFilleulMail.html.twig',
                        [
                            'user_name' => $filleul->getUserName(),
                            'email' => $filleul->getEmail(),
                            'parrain' => $filleul->getParrain(),
                            'indirect' => $filleul->getIndirect()
                        ]
                    ),
                    'text/html'
                );
            $this->mailer->send($message);
	}
}
