<?php

namespace Astina\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class RecaptchaType extends AbstractType
{

    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $secretKey;


    /**
     * RecaptchaType constructor.
     *
     * @param $siteKey
     * @param $secretKey
     */
    public function __construct($siteKey)
    {
        $this->siteKey = $siteKey;
    }


    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['key'] = $this->siteKey;

        return parent::buildView($view, $form, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mapped' => false,
            'label' => false,
        ));
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'text';
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'recaptcha';
    }

}
