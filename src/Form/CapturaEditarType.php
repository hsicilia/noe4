<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class CapturaEditarType extends CapturaCrearType
{
    protected function especifico(FormBuilderInterface $formBuilder): void
    {
        $formBuilder
            ->add('borrarImagen', CheckboxType::class, [
                'mapped' => false,
                'label' => 'captura.campo.borrarImagen',
                'required' => false,
            ]);
    }
}
