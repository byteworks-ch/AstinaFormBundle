<?php

namespace Astina\Bundle\FormBundle\Form;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormConfigField;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormConfigType extends AbstractType
{
    /**
     * @var EntityRepository
     */
    protected $fieldRepository;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    function __construct(EntityRepository $fieldRepository, RegistryInterface $doctrine)
    {
        $this->fieldRepository = $fieldRepository;
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('title')
            ->add('successLink', 'webcms_link')
        ;

        $builder->get('successLink')
            ->remove('text')
            ->remove('target')
        ;

        $builder->add('notificationAddress');

        $builder
            ->add('fields', 'collection', array(
                'type' => 'webcms_form_config_field',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
        ;

        $builder->add('submitLabel');
        
        // confirmation email 
        $builder
            ->add('confirmation_email_enabled', 'choice', array(
                'choices' => array(0 => 'no', 1 => 'yes'),
                'help' => 'form_help_confirmation_email_enabled',
            ))
            ->add('confirmation_email_subject', 'text', array(
                'required' => false,
            ))
            ->add('confirmation_email_body', 'textarea', array(
                'required' => false,
                'attr' => array('style' => 'height: 200px;'),
                'help' => 'form_help_confirmation_email_body',
            ))
        ;

        $this->addEventListeners($builder);
    }

    protected function addEventListeners(FormBuilderInterface $builder)
    {
        $fieldRepository = $this->fieldRepository;
        $doctrine = $this->doctrine;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) use ($fieldRepository, $doctrine) {

            /** @var FormConfig $formConfig */
            $formConfig = $event->getData();

            // remove orphaned fields

            $fields = $formConfig->getFields();
            $currentFields = $fieldRepository->findBy(array('formConfig' => $formConfig));

            /** @var FormConfigField $currentField */
            foreach ($currentFields as $currentField) {
                foreach ($fields as $field) {
                    if ($field->getId() === $currentField->getId()) {
                        continue 2;
                    }
                }
                $doctrine->getManager()->remove($currentField);
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Astina\Bundle\FormBundle\Entity\FormConfig',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'webcms_form_config';
    }
}