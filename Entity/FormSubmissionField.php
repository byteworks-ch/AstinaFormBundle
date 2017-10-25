<?php

namespace Astina\Bundle\FormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class FormSubmissionField
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var FormSubmission
     * @ORM\ManyToOne(targetEntity="FormSubmission")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $submission;

    /**
     * @var FormConfigField
     * @ORM\ManyToOne(targetEntity="FormConfigField")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $configField;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param FormSubmission $submission
     */
    public function setSubmission(FormSubmission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * @return FormSubmission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param FormConfigField $configField
     */
    public function setConfigField(FormConfigField $configField = null)
    {
        $this->configField = $configField;
    }

    /**
     * @return FormConfigField
     */
    public function getConfigField()
    {
        return $this->configField;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $value = implode('; ', $value);
        }
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}