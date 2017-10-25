<?php

namespace Astina\Bundle\FormBundle\Mail;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormSubmission;
use Symfony\Component\Templating\EngineInterface;

class NotificationMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    protected $template;

    protected $fromAddress;

    function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $template, $fromAddress)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->template = $template;
        $this->fromAddress = $fromAddress;
    }

    public function send(FormConfig $formConfig, FormSubmission $submission)
    {
        $recipients = $this->parseRecipients($formConfig);

        $message = \Swift_Message::newInstance()
            ->setSubject($formConfig->getName())
            ->setFrom($this->fromAddress)
            ->setTo($recipients)
            ->setBody(
                $this->templating->render($this->template, array(
                    'config' => $formConfig,
                    'submission' => $submission,
                )),
                'text/html',
                'utf-8'
            )
        ;

        $this->mailer->send($message);
    }

    protected function parseRecipients(FormConfig $formConfig)
    {
        $addresses = $formConfig->getNotificationAddress();

        $addresses = preg_replace('/[,]/', ';', $addresses);
        $addresses = explode(';', $addresses);
        $addresses = array_map('trim', $addresses);

        return $addresses;
    }
}
