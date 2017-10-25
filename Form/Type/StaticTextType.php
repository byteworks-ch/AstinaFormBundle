<?php

namespace Astina\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StaticTextType extends AbstractType
{

    public function getParent()
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'static_text';
    }

}
