<?php

namespace Astina\Bundle\FormBundle\Form;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Event\FormEvent;
use Astina\Bundle\FormBundle\Event\FormEvents;
use Astina\Bundle\WebcmsBundle\Routing\WebcmsRouter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormTracker
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var WebcmsRouter
     */
    protected $webcmsRouter;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    protected $formType;

    /**
     * @var FormInterface[]
     */
    protected $forms = array();

    function __construct(FormFactoryInterface $formFactory, WebcmsRouter $webcmsRouter, EventDispatcherInterface $dispatcher, $formType)
    {
        $this->formFactory = $formFactory;
        $this->webcmsRouter = $webcmsRouter;
        $this->dispatcher = $dispatcher;
        $this->formType = $formType;
    }

    public function getForm(FormConfig $formConfig)
    {
        if (array_key_exists($formConfig->getId(), $this->forms)) {
            return $this->forms[$formConfig->getId()];
        }

        $form = $this->formFactory->create($this->formType, null, array(
            'form_config' => $formConfig,
        ));

        $this->forms[$formConfig->getId()] = $form;

        return $form;
    }

    public function handleRequest(FormConfig $formConfig, Request $request)
    {
        $form = $this->getForm($formConfig);

        if ($success = $form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            $data['browser'] = $request->headers->get('User-Agent');
            $data['ip'] = $request->getClientIp();

            $this->dispatcher->dispatch(FormEvents::SUBMIT_SUCCESS, new FormEvent($formConfig, $data));
        }

        return $success;
    }
}