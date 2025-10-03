<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioBuscarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('usuario', TextType::class, [
                'label' => 'usuario.campo.usuario',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'usuario.campo.email',
                'required' => false,
            ])
            ->add('nombre', TextType::class, [
                'label' => 'usuario.campo.nombre',
                'required' => false,
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'usuario.campo.organizacion',
                'required' => false,
            ])
            ->add('enviar', SubmitType::class, [
                'label' => 'formulario.enviar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
