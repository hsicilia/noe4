<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioPerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'usuario.error.password_norep',
                'first_options' => ['label' => 'usuario.campo.password'],
                'second_options' => ['label' => 'usuario.campo.password_rep'],
                'required' => false,
                'mapped' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'usuario.campo.email',
            ])
            ->add('nombre', TextType::class, [
                'label' => 'usuario.campo.nombre',
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'usuario.campo.organizacion',
            ])
            ->add('lugarDefecto', TextType::class, [
                'label' => 'captura.campo.lugarCaptura',
                'required' => false,
            ])
            ->add('geoLatDefecto', NumberType::class, [
                'label' => 'captura.campo.geoLatCaptura',
                'scale' => 12,
                'required' => false,
            ])
            ->add('geoLongDefecto', NumberType::class, [
                'label' => 'captura.campo.geoLongCaptura',
                'scale' => 12,
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
