<?php

namespace Astina\Bundle\FormBundle\Form;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Astina\Bundle\FormBundle\Entity\FormConfigField;
use Astina\Bundle\WebcmsBundle\Routing\WebcmsRouter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormType extends AbstractType
{

    /**
     * @var WebcmsRouter
     */
    private $webcmsRouter;

    const FORM_CONFIG_ID_FIELD = '_form_config_id';


    function __construct($webcmsRouter, $recaptchaSiteKey)
    {
        $this->webcmsRouter = $webcmsRouter;
        $this->recaptchaSiteKey = $recaptchaSiteKey;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FormConfig $formConfig */
        $formConfig = $options['form_config'];

        $builder->setAction($this->webcmsRouter->generate($formConfig->getPage()));

        foreach ($formConfig->getFields() as $field) {
            $this->addField($builder, $field);
        }

        $builder->add(self::FORM_CONFIG_ID_FIELD, 'hidden', array(
            'data' => $formConfig->getId(),
        ));

        if (!empty($this->recaptchaSiteKey)) {
            $builder->add('recaptcha', 'recaptcha');
        }

        $builder->add('send', 'submit', array(
            'label' => ($formConfig->getSubmitLabel() ?: 'send'),
        ));
    }

    protected function addField(FormBuilderInterface $builder, FormConfigField $field)
    {
        $type = $this->getFieldType($field);

        if (strpos($type, 'static') === 0) {
            $options = array(
                'label'       => false,
                'data'        => $field->getOptions2(),
                'empty_data'  => $field->getOptions2(),
                'required'    => false,
            );
        }
        else {
            $options = array_merge(array(
                'label'       => $field->getName(),
                'required'    => $field->getMandatory(),
                'constraints' => $this->createFieldConstraints($field),
                'data'        => $field->getDefaultValue(),
                'help'        => $field->getHelp(),
            ), $this->createAdditionalFieldOptions($field));
        }

        $builder->add($field->getId(), $type, $options);
    }

    protected function getFieldType(FormConfigField $field)
    {
        $type = $field->getType();

        if (strpos($type, 'choice') === 0) {
            return 'choice';
        }

        return $type;
    }

    protected function createFieldConstraints(FormConfigField $field)
    {
        $constraints = array();

        if ($field->getMandatory()) {
            $constraints[] = new Assert\NotBlank();
        }
        if ($field->getType() == 'email') {
            $constraints[] = new Assert\Email();
        }

        return $constraints;
    }

    protected function createAdditionalFieldOptions(FormConfigField $field)
    {
        $options = array();

        switch ($field->getType()) {
            case 'date':
                $options['widget'] = 'single_text';
                $options['format'] = 'dd.MM.yyyy';
                break;

            case 'choice_checkbox':
                $options['choices'] = $this->createFieldChoices($field->getOptions1());
                $options['multiple'] = true;
                $options['expanded'] = true;
                break;

            case 'choice_radio':
                $options['choices'] = $this->createFieldChoices($field->getOptions1());
                $options['multiple'] = false;
                $options['expanded'] = true;
                break;

            case 'choice_select_multiple':
                $options['choices'] = $this->createFieldChoices($field->getOptions1());
                $options['multiple'] = true;
                $options['expanded'] = false;
                break;

            case 'choice_select_single':
                $options['choices'] = $this->createFieldChoices($field->getOptions1());
                $options['multiple'] = false;
                $options['expanded'] = false;
                break;
        }

        if (isset($options['choices']) && count($options['choices']) == 1) {
            $options['label'] = ' ';
        }

        return $options;
    }

    protected function createFieldChoices($options)
    {
        $options = explode(';', $options);
        $options = array_map('trim', $options);

        $choices = array();

        foreach ($options as $option) {
            if (preg_match('/^(.*)\[(.*?)=(.*)\](.*)/', $option, $temp)) {
                $option = sprintf('%s<a href="%s" target="_blank">%s</a>%s', $temp[1], $temp[3], $temp[2], $temp[4]);
            }

            $choices[$option] = $option;
        }

        return $choices;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'form_config' => null,
        ));
    }

    public function getName()
    {
        return 'webcms_form';
    }

}
