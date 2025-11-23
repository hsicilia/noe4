<?php

namespace App\Form;

use App\Classes\Constantes;
use App\Entity\Especie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EspecieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'especie.campo.nombre',
                'required' => $options['required_fields'],
            ])
            ->add('comun', TextType::class, [
                'label' => 'especie.campo.comun',
                'required' => $options['required_fields'],
            ])
            ->add('invasora', ChoiceType::class, [
                'label' => 'ejemplar.campo.invasora',
                'choices' => array_flip(Constantes::$opciones_invasora),
                'required' => $options['required_fields'],
            ])
            ->add('cites', ChoiceType::class, [
                'label' => 'ejemplar.campo.cites',
                'choices' => array_flip(Constantes::$opciones_cites),
                'required' => $options['required_fields'],
            ])
            ->add('peligroso', ChoiceType::class, [
                'label' => 'ejemplar.campo.peligroso',
                'choices' => array_flip(Constantes::$opciones_peligroso),
                'required' => $options['required_fields'],
            ])
            ->add('enviar', SubmitType::class, [
                'label' => 'formulario.enviar',
            ]);

        // Agregar botones de informe solo si es formulario de búsqueda
        if (! $options['required_fields']) {
            $builder
                ->add('informePDF', SubmitType::class, [
                    'label' => 'formulario.informe_pdf',
                ])
                ->add('informeCSV', SubmitType::class, [
                    'label' => 'formulario.informe_csv',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Especie::class,
            'required_fields' => true,
        ]);
    }
}
