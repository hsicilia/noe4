<?php

namespace App\Form;

use App\Classes\Constantes;
use App\Entity\Ejemplar;
use App\Entity\Especie;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusquedaMapaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lat = $options['latitud'];
        $long = $options['longitud'];
        $builder
            ->add('especie', EntityType::class, [
                'label' => 'ejemplar.campo.especie',
                'class' => Especie::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.nombre', 'ASC');
                },
                'choice_label' => 'nombre',
                'required' => false,
            ])
            ->add('sexo', ChoiceType::class, [
                'label' => 'ejemplar.campo.sexo',
                'choices' => array_flip(Constantes::$opciones_sexo),
            ])
            ->add('fechaInicial', DateType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.fechaInicial',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('fechaFinal', DateType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.fechaFinal',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('recinto', TextType::class, [
                'label' => 'ejemplar.campo.recinto',
                'required' => false,
            ])
            ->add('lugar', TextType::class, [
                'label' => 'ejemplar.campo.lugar',
                'required' => false,
            ])
            ->add('distancia', NumberType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.distancia',
                'required' => false,
            ])
            ->add('geoLat', NumberType::class, [
                'label' => 'ejemplar.campo.geoLat',
                'data' => $lat,
                'scale' => 12,
                'attr' => ['readonly' => true],
            ])
            ->add('geoLong', NumberType::class, [
                'label' => 'ejemplar.campo.geoLong',
                'data' => $long,
                'scale' => 12,
                'attr' => ['readonly' => true],
            ])
            ->add('origen', ChoiceType::class, [
                'label' => 'ejemplar.campo.origen',
                'choices' => array_flip(Constantes::$opciones_origen),
            ])
            ->add('documentacion', ChoiceType::class, [
                'label' => 'ejemplar.campo.documentacion',
                'choices' => array_flip(Constantes::$opciones_documentacion),
            ])
            ->add('progenitor1', TextType::class, [
                'label' => 'busquedamapa.campo.progenitor',
                'required' => false,
            ])
            ->add('depositoNombre', TextType::class, [
                'label' => 'ejemplar.campo.depositoNombre',
                'required' => false,
            ])
            ->add('depositoDNI', TextType::class, [
                'label' => 'ejemplar.campo.depositoDNI',
                'required' => false,
            ])
            ->add('invasora', ChoiceType::class, [
                'label' => 'ejemplar.campo.invasora',
                'choices' => array_flip(Constantes::$opciones_invasora),
                'required' => false,
            ])
            ->add('cites', ChoiceType::class, [
                'label' => 'ejemplar.campo.cites',
                'choices' => array_flip(Constantes::$opciones_cites),
                'required' => false,
            ])
            ->add('peligroso', ChoiceType::class, [
                'label' => 'ejemplar.campo.peligroso',
                'choices' => array_flip(Constantes::$opciones_peligroso),
                'required' => false,
            ])
            ->add('tipoEjemplar', ChoiceType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.tipoEjemplar',
                'choices' => [
                    'busquedamapa.opcion.todos' => 'todos',
                    'busquedamapa.opcion.alta' => 'alta',
                    'busquedamapa.opcion.baja' => 'baja',
                ],
                'data' => 'alta',
                'required' => true,
            ])
            ->add('fechaBajaInicial', DateType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.fechaBajaInicial',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('fechaBajaFinal', DateType::class, [
                'mapped' => false,
                'label' => 'busquedamapa.campo.fechaBajaFinal',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('causaBaja', ChoiceType::class, [
                'label' => 'ejemplar.campo.causaBaja',
                'choices' => array_flip(Constantes::$causa_baja),
                'required' => false,
            ])
            ->add('buscar', SubmitType::class, [
                'label' => 'formulario.buscar',
            ])
            ->add('informe', SubmitType::class, [
                'label' => 'busquedamapa.generar_informe',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ejemplar::class,
            'method' => 'GET',
            'latitud' => null,
            'longitud' => null,
        ]);

        $resolver->setRequired(['latitud', 'longitud']);
        $resolver->setAllowedTypes('latitud', ['float', 'null']);
        $resolver->setAllowedTypes('longitud', ['float', 'null']);
    }
}
