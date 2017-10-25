<?php

namespace Astina\Bundle\FormBundle\Twig;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Form\FormTracker;
use Symfony\Component\Form\FormFactoryInterface;

class FormExtension extends \Twig_Extension
{
    /**
     * @var FormTracker
     */
    protected $formTracker;

    function __construct(FormTracker $formTracker)
    {
        $this->formTracker = $formTracker;
    }

    public function getFunctions()
    {
        return array(
            'webcms_form' => new \Twig_Function_Method($this, 'getForm'),
        );
    }

    public function getForm(FormConfig $formConfig)
    {
        $form = $this->formTracker->getForm($formConfig);

        return $form->createView();
    }

    public function getName()
    {
        return 'astina_form';
    }
}