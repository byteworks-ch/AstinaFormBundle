<?php

namespace Astina\Bundle\FormBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormConfigFieldType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'field_name'))
            ->add('type', 'choice', array(
                'choices' => array(
                    'fields' => array(
                        'text'                   => 'text',
                        'textarea'               => 'textarea',
                        'email'                  => 'email',
                        'date'                   => 'date',
                        'choice_checkbox'        => 'choice_checkbox',
                        'choice_radio'           => 'choice_radio',
                        'choice_select_single'   => 'choice_select_single',
                        'choice_select_multiple' => 'choice_select_multiple',
                    ),
                    'labels' => array(
                        'static_title' => 'static_title',
                        'static_text'  => 'static_text',
                    )
                )
            ))
            ->add('help', 'textarea')
            ->add('options1', 'text')
            ->add('options2', 'textarea')
            ->add('mandatory', 'checkbox', array(
                'required' => false,
            ))
            ->add('position', 'hidden')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Astina\Bundle\FormBundle\Entity\FormConfigField',
            'translation_domain' => 'form',
        ));
    }

    public function getName()
    {
        return 'webcms_form_config_field';
    }

}
