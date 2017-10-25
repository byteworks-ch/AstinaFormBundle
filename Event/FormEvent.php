<?php

namespace Astina\Bundle\FormBundle\Event;

use Astina\Bundle\FormBundle\Entity\FormConfig;
use Symfony\Component\EventDispatcher\Event;

class FormEvent extends Event
{
    /**
     * @var FormConfig
     */
    private $formConfig;

    private $data;

    function __construct(FormConfig $formConfig, array $data)
    {
        $this->formConfig = $formConfig;
        $this->data = $data;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    public function getData()
    {
        return $this->data;
    }
}