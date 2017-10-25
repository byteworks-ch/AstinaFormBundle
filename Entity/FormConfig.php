<?php

namespace Astina\Bundle\FormBundle\Entity;

use Astina\Bundle\WebcmsBundle\Entity\Link;
use Astina\Bundle\WebcmsBundle\Entity\PageContent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class FormConfig extends PageContent
{

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var Link
     * @ORM\OneToOne(targetEntity="Astina\Bundle\WebcmsBundle\Entity\Link", cascade={"persist"})
     */
    private $successLink;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $notificationAddress;

    /**
     * @var FormConfigField[]
     * @ORM\OneToMany(targetEntity="FormConfigField", mappedBy="formConfig", cascade={"all"})
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $fields;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $submitLabel;

    /**
     * @var FormSubmission
     * @ORM\OneToMany(targetEntity="FormSubmission", mappedBy="formConfig", cascade={"all"}, fetch="LAZY")
     * @ORM\OrderBy({"created"="DESC"})
     */
    private $submissions;


    function __construct()
    {
        parent::__construct();

        $this->fields = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();

        if ($this->successLink) {
            $this->successLink = clone $this->successLink;
        }
    }

    public function getFormType()
    {
        return 'webcms_form_config';
    }
    
    /**
     * return a form theme that will be added when displaying this admin form
     * important: AstinaWebcmsBundle:Form:content.html.twig will be applied on top!
     * @return string
     */
    public function getFormTheme()
    {
        return 'AstinaFormBundle:Form:admin_theme.html.twig';
    }    
    

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Link $successLink
     */
    public function setSuccessLink(Link $successLink)
    {
        $this->successLink = $successLink;
    }

    /**
     * @return Link
     */
    public function getSuccessLink()
    {
        return $this->successLink;
    }

    public function setNotificationAddress($notificationAddress)
    {
        $this->notificationAddress = $notificationAddress;
    }

    public function getNotificationAddress()
    {
        return $this->notificationAddress;
    }

    /**
     * @param FormConfigField[] $fields
     */
    public function setFields($fields)
    {
        foreach ($fields as $i => $field) {
            // HACK for importing yaml data
            if (is_array($field)) {
                $field = FormConfigField::fromArray($field);
                $fields[$i] = $field;
            }
            $field->setFormConfig($this);
        }
        if (!is_object($fields)) {
            $fields = new ArrayCollection($fields);
        }

        $this->fields = $fields;
    }

    /**
     * @return FormConfigField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function setSubmitLabel($submitLabel)
    {
        $this->submitLabel = $submitLabel;
    }

    public function getSubmitLabel()
    {
        return $this->submitLabel;
    }

    public function setSubmissions($submissions)
    {
        $this->submissions = $submissions;
    }

    public function getSubmissions()
    {
        return $this->submissions;
    }

}
