<?php

namespace Astina\Bundle\FormBundle\Mail;
use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormSubmission;
use Symfony\Bridge\Monolog\Logger;

/**
 * sends a confirmation the user that filled the form 
 * used in FormBuilder
 */
class ConfirmationMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $fromAddress;

    /**
     * @var Logger
     */
    protected $logger;
    
    
    /**
     * FormConfirmationMailer constructor.
     * @param \Swift_Mailer $mailer
     * @param $fromAddress
     */
    function __construct(\Swift_Mailer $mailer, $fromAddress, Logger $logger)
    {
        $this->mailer = $mailer;
        $this->fromAddress = $fromAddress;
        $this->logger = $logger;
    }


    /**
     * @param FormConfig $formConfig
     * @param FormSubmission $submission
     */
    public function send(FormConfig $formConfig, FormSubmission $submission)
    {
        $submittedData = array();
        foreach ($submission->getFields() as $field) {
            
            // first email field is receiver
            if($field->getConfigField()->getType() === 'email' && !isset($toAddress)) {
                $toAddress = $field->getValue();
            }
            
            // collect submitted values
            if(strpos($field->getConfigField()->getType(), 'choice_') === 0) {
                $submittedData[$field->getName()] = str_replace(';', "\n", $field->getValue());
            }
            else {
                $submittedData[$field->getName()] = $field->getValue();
            }
        }
        $submittedData['Counter'] = $submission->getCounter();
        
        
        // no email address found
        if (!isset($toAddress)) {
            $this->logger->error(
                'Confirmation mail enabled in form "{name}" but no email address found in form config', 
                array('name' => $formConfig->getName())
            );
            return;
        }
        
        $subject = $formConfig->getAttribute('confirmation_email_subject')->getValue();
        $bodyTemplate = $formConfig->getAttribute('confirmation_email_body')->getValue();
        
        
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->fromAddress)
            ->setTo($toAddress)
            ->setBody($this->replacePlaceholder($bodyTemplate, $submittedData),
                'text/plain',
                'utf-8'
            )
        ;
        
        $this->mailer->send($message);
    }


    /**
     * replaces all placeholders with values filled by the form user
     * placeholders are enclosed by squared brackets
     * e.g. '[field name]' to 'field value'
     * 
     * @param $template
     * @param array $submittedData
     * @return string
     */
    public function replacePlaceholder($template, array $submittedData)
    {
        $replacements = array();
        foreach ($submittedData as $key => $value) {
            $replacements['['.$key.']'] = $value;
        }
        
        return strtr($template, $replacements);
    }
}
