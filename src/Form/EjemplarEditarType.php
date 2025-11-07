<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class EjemplarEditarType extends EjemplarCrearType
{
    /**
     * Añade campos específicos del formulario de edición
     * (checkboxes para borrar imágenes)
     */
    protected function especifico(FormBuilderInterface $formBuilder): void
    {
        $formBuilder
            ->add('borrarImagen1', CheckboxType::class, [
                'mapped' => false,
                'label' => 'ejemplar.campo.borrarImagen1',
                'required' => false,
            ])
            ->add('borrarImagen2', CheckboxType::class, [
                'mapped' => false,
                'label' => 'ejemplar.campo.borrarImagen2',
                'required' => false,
            ])
            ->add('borrarImagen3', CheckboxType::class, [
                'mapped' => false,
                'label' => 'ejemplar.campo.borrarImagen3',
                'required' => false,
            ]);
    }
}
