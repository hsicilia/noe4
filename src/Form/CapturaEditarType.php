<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class CapturaEditarType extends CapturaCrearType
{
    protected function especifico(FormBuilderInterface $builder): void
    {
        $builder
            ->add('borrarImagen', CheckboxType::class, [
                'mapped' => false,
                'label' => 'captura.campo.borrarImagen',
                'required' => false,
            ]);
    }
}
