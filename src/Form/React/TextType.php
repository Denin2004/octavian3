<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReactTextType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }

    public function reactView()
    {

    }
}
