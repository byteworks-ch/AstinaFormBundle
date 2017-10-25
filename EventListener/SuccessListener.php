<?php

namespace Astina\Bundle\FormBundle\EventListener;

use Astina\Bundle\FormBundle\Entity\FormSubmissionRepository;
use Astina\Bundle\FormBundle\Event\FormEvent;
use Astina\Bundle\FormBundle\Mail\ConfirmationMailer;
use Astina\Bundle\FormBundle\Mail\NotificationMailer;

class SuccessListener
{
    /**
     * @var FormSubmissionRepository
     */
    protected $submissionRepository;
    
    /**
     * @var NotificationMailer
     */
    protected $notificationMailer;
    
    /**
     * @var NotificationMailer
     */
    protected $confirmationMailer;

    /**
     * SuccessListener constructor.
     * @param FormSubmissionRepository $submissionRepository
     * @param NotificationMailer $notificationMailer
     * @param ConfirmationMailer $confirmationMailer
     */
    public function __construct(
        FormSubmissionRepository $submissionRepository, 
        NotificationMailer $notificationMailer,
        ConfirmationMailer $confirmationMailer
    )
    {
        $this->submissionRepository = $submissionRepository;
        $this->notificationMailer = $notificationMailer;
        $this->confirmationMailer = $confirmationMailer;
    }


    /**
     * @param FormEvent $event
     */
    public function onSuccess(FormEvent $event)
    {
        $formConfig = $event->getFormConfig();
        $formData = $event->getData();

        $submission = $this->submissionRepository
            ->saveFormData($formConfig, $formData);

        // send notification
        if ($formConfig->getNotificationAddress()) {
            $this->notificationMailer->send($formConfig, $submission);
        }
        
        // send confirmation
        $enabledAttribute = $formConfig->getAttribute('confirmation_email_enabled');
        if ($enabledAttribute && true == (bool) $enabledAttribute->getValue()) {
            $this->confirmationMailer->send($formConfig, $submission);
        }
    }
} 