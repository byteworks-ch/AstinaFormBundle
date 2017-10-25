<?php

namespace Astina\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StaticTitleType extends AbstractType
{

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'static_title';
    }

}
