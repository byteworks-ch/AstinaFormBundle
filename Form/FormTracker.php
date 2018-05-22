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

    /**
     * @var strings
     */
    protected $formType;

    /**
     * @var string
     */
    protected $recaptchaSecretKey;


    /**
     * @var FormInterface[]
     */
    protected $forms = [];


    /**
     * FormTracker constructor.
     *
     * @param FormFactoryInterface     $formFactory
     * @param WebcmsRouter             $webcmsRouter
     * @param EventDispatcherInterface $dispatcher
     * @param                          $formType
     * @param                          $recaptchaSecretKey
     */
    function __construct(FormFactoryInterface $formFactory, WebcmsRouter $webcmsRouter, EventDispatcherInterface $dispatcher, $formType, $recaptchaSecretKey)
    {
        $this->formFactory        = $formFactory;
        $this->webcmsRouter       = $webcmsRouter;
        $this->dispatcher         = $dispatcher;
        $this->formType           = $formType;
        $this->recaptchaSecretKey = $recaptchaSecretKey;
    }


    /**
     * @param FormConfig $formConfig
     *
     * @return FormInterface
     */
    public function getForm(FormConfig $formConfig)
    {
        if (array_key_exists($formConfig->getId(), $this->forms)) {
            return $this->forms[$formConfig->getId()];
        }

        $form = $this->formFactory->create($this->formType, null, [
            'form_config' => $formConfig,

        ]);

        $this->forms[$formConfig->getId()] = $form;

        return $form;
    }


    /**
     * @param FormConfig $formConfig
     * @param Request    $request
     *
     * @return bool
     */
    public function handleRequest(FormConfig $formConfig, Request $request)
    {
        $form = $this->getForm($formConfig);

        if ($success = $form->handleRequest($request)->isValid()) {
            if ($success = $this->isCaptchaValid($request->get('g-recaptcha-response'))) {
                $data            = $form->getData();
                $data['browser'] = $request->headers->get('User-Agent');
                $data['ip']      = $request->getClientIp();

                $this->dispatcher->dispatch(FormEvents::SUBMIT_SUCCESS, new FormEvent($formConfig, $data));
            }
        }

        return $success;
    }


    /**
     * Get success response from recaptcha and return it to controller
     *
     * @param $recaptcha
     *
     * @return mixed
     */
    protected function isCaptchaValid($recaptcha) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('secret' => $this->recaptchaSecretKey, 'response' => $recaptcha));

        $temp = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($temp);

        return $data->success;
    }

}
