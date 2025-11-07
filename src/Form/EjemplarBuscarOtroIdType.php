<?php

namespace App\Form;

use App\Entity\Ejemplar;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EjemplarBuscarOtroIdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('otroId', TextType::class, [
                'mapped' => false,
                'label' => 'ejemplar.campo.otroId',
                'required' => false,
            ])
            ->add('buscar', SubmitType::class, [
                'label' => 'formulario.buscar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ejemplar::class,
        ]);
    }
}
