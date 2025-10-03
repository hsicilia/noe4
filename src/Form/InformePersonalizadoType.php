<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InformePersonalizadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fechaInicio', DateType::class, [
                'label' => 'informe.campo.fechaInicio',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('fechaFin', DateType::class, [
                'label' => 'informe.campo.fechaFin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('invasores', ChoiceType::class, [
                'label' => 'informe.campo.invasores',
                'choices' => [
                    'informe.opciones.todos' => 'todos',
                    'informe.opciones.solo_invasores' => 'si',
                    'informe.opciones.no_invasores' => 'no',
                ],
                'data' => 'todos',
            ])
            ->add('cites', ChoiceType::class, [
                'label' => 'informe.campo.cites',
                'choices' => [
                    'informe.opciones.todos' => 'todos',
                    'informe.opciones.solo_cites' => 'si',
                    'informe.opciones.no_cites' => 'no',
                ],
                'data' => 'todos',
            ])
            ->add('buscar', SubmitType::class, [
                'label' => 'formulario.buscar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}
