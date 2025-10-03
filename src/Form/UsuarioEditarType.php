<?php

namespace App\Form;

use App\Classes\Constantes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UsuarioEditarType extends UsuarioCrearType
{
    protected function especifico(FormBuilderInterface $builder): void
    {
        $builder
            ->add('activado', ChoiceType::class, [
                'label' => 'usuario.campo.activado',
                'choices' => array_flip(Constantes::$opciones_usuario_activado),
            ]);
    }
}
