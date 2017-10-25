<?php

namespace Astina\Bundle\FormBundle\EventListener;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Form\FormTracker;
use Astina\Bundle\FormBundle\Form\FormType;
use Astina\Bundle\WebcmsBundle\Routing\WebcmsRouter;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubmitListener
{
    /**
     * @var FormTracker
     */
    protected $formTracker;

    /**
     * @var EntityRepository
     */
    protected $formConfigRepository;

    /**
     * @var WebcmsRouter
     */
    protected $webcmsRouter;

    protected $formType;

    function __construct(FormTracker $formTracker, EntityRepository $formConfigRepository, WebcmsRouter $webcmsRouter, $formType)
    {
        $this->formTracker = $formTracker;
        $this->formConfigRepository = $formConfigRepository;
        $this->formType = $formType;
        $this->webcmsRouter = $webcmsRouter;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        if (null == ($data = $request->get($this->formType))) {
            return;
        }

        if (!isset($data[FormType::FORM_CONFIG_ID_FIELD])) {
            return;
        }

        $formConfigId = $data[FormType::FORM_CONFIG_ID_FIELD];
        /** @var FormConfig $formConfig */
        $formConfig = $this->formConfigRepository->find($formConfigId);
        if (null == $formConfig) {
            // TODO throw exception or just log?
            return;
        }

        $success = $this->formTracker->handleRequest($formConfig, $request);

        if ($success) {
            $successUrl = $this->getSuccessUrl($formConfig);
            $event->setResponse(new RedirectResponse($successUrl));
        }
    }

    private function getSuccessUrl(FormConfig $formConfig)
    {
        $successLink = $formConfig->getSuccessLink();

        if (!$successLink) {
            return $this->webcmsRouter->generate($formConfig->getPage());
        }

        if ($node = $successLink->getSiteNode()) {
            return $this->webcmsRouter->generate($node);
        }

        if ($url = $successLink->getUrl()) {
            return $url;
        }

        return $this->webcmsRouter->generate($formConfig->getPage());
    }
}