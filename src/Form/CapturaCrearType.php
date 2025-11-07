<?php

namespace App\Form;

use App\Entity\Captura;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CapturaCrearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->especifico($builder);

        $builder
            ->add('tipoCaptura', TextType::class, [
                'label' => 'captura.campo.tipoCaptura',
            ])
            ->add('fechaCaptura', DateType::class, [
                'label' => 'captura.campo.fechaCaptura',
                'widget' => 'single_text',
            ])
            ->add('horaCaptura', TimeType::class, [
                'label' => 'captura.campo.horaCaptura',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('lugarCaptura', TextType::class, [
                'label' => 'captura.campo.lugarCaptura',
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'captura.campo.observaciones',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('imagen', FileType::class, [
                'label' => 'captura.campo.imagen',
                'required' => false,
                'mapped' => false,
            ])
            ->add('fotosURL', UrlType::class, [
                'label' => 'captura.campo.fotosURL',
                'required' => false,
            ])
            ->add('enviar', SubmitType::class, [
                'label' => 'formulario.enviar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Captura::class,
        ]);
    }

    protected function especifico(FormBuilderInterface $formBuilder): void
    {
        // Se sobreescribe en CapturaEditarType
    }
}
