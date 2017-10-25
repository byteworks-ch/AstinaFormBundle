<?php

namespace Astina\Bundle\FormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class FormConfigField
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var FormConfig
     * @ORM\ManyToOne(targetEntity="FormConfig", inversedBy="fields")
     * @ORM\JoinColumn(onDelete="cascade")
     * @Assert\NotBlank()
     */
    private $formConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $help;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $options1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $options2;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $mandatory;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $defaultValue;


    function __construct()
    {
        $this->mandatory = false;
        $this->position = 0;
    }

    /**
     * @param $data
     * @throws \Exception
     * @return FormConfigField
     */
    public static function fromArray($data)
    {
        if (!isset($data['name']) || !isset($data['type'])) {
            throw new \Exception('Need name and type in order to create FormConfigField');
        }

        $field = new FormConfigField();
        $field->setName($data['name']);
        $field->setType($data['type']);
        $field->setOptions1($data['options1']);
        $field->setOptions2($data['options2']);
        $field->setMandatory(isset($data['mandatory']) ? (bool) $data['mandatory'] : false);
        $field->setDefaultValue(isset($data['defaultValue']) ? $data['defaultValue'] : null);

        return $field;
    }

    function __toString()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param FormConfig $form
     */
    public function setFormConfig(FormConfig $form)
    {
        $this->formConfig = $form;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setHelp($help)
    {
        $this->help = $help;
    }

    public function getHelp()
    {
        return $this->help;
    }

    public function setOptions1($options1)
    {
        $this->options1 = $options1;
    }

    public function getOptions1()
    {
        return $this->options1;
    }

    public function setOptions2($options2)
    {
        $this->options2 = $options2;
    }

    public function getOptions2()
    {
        return $this->options2;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

} 
