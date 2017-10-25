<?php

namespace Astina\Bundle\FormBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Astina\Bundle\FormBundle\Entity\FormSubmissionRepository")
 */
class FormSubmission
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var FormConfig
     * @ORM\ManyToOne(targetEntity="FormConfig", inversedBy="submissions")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $formConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $formName;

    /**
     * @var FormSubmissionField[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="FormSubmissionField", cascade={"all"}, mappedBy="submission")
     */
    private $fields;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $browser;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $created;

    /**
     * submission number of a form config
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $counter;
    

    /**
     * @var array
     */
    private $_fieldCache;

    function __construct()
    {
        $this->created = new \DateTime();
        $this->fields = new ArrayCollection();
        $this->counter = 1;
    }

    public function getSummary()
    {
        $summary = array();

        foreach ($this->fields as $field) {
            $value = $field->getValue();
            if (strlen($value) > 100) {
                $value = substr($value, 96, ' ...');
            }
            $summary[] = sprintf('%s: %s', $field->getName(), $value);
        }

        return implode('; ', $summary);
    }

    public function findValue(FormConfigField $configField)
    {
        if (null === $this->_fieldCache) {
            $this->createFieldCache();
        }

        if (isset($this->_fieldCache[$configField->getId()])) {
            return $this->_fieldCache[$configField->getId()];
        }

        if (isset($this->_fieldCache['config#' . $configField->getName()])) {
            return $this->_fieldCache['config#' . $configField->getName()];
        }

        if (isset($this->_fieldCache['submission#' . $configField->getName()])) {
            return $this->_fieldCache['submission#' . $configField->getName()];
        }

        return null;
    }

    private function createFieldCache()
    {
        $this->_fieldCache = array();
        foreach ($this->fields as $field) {
            if ($configField = $field->getConfigField()) {
                $this->_fieldCache[$configField->getId()] = $field->getValue();
                $this->_fieldCache['config#' . $configField->getName()] = $field->getValue();
            }
            $this->_fieldCache['submission#' . $field->getName()] = $field->getValue();
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param FormConfig $formConfig
     */
    public function setFormConfig(FormConfig $formConfig)
    {
        $this->formConfig = $formConfig;
        $this->setFormName($formConfig->getName());
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    /**
     * @param $formName
     */
    public function setFormName($formName)
    {
        $this->formName = $formName;
    }

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * @param  FormSubmissionField[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return FormSubmissionField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $browser
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    }

    /**
     * @return mixed
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @return integer
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param integer $counter
     * @return $this
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
        return $this;
    }

    /**
     * @param FormSubmissionField $field
     */
    public function addField(FormSubmissionField $field)
    {
        $field->setSubmission($this);
        $this->fields->add($field);
    }
} 
