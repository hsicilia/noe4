<?php

namespace App\Form;

use Doctrine\ORM\QueryBuilder;
use App\Classes\Constantes;
use App\Entity\Ejemplar;
use App\Entity\Especie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EjemplarCrearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->especifico($builder);

        $builder
            ->add('fechaRegistro', DateType::class, [
                'label' => 'ejemplar.campo.fechaRegistro',
                'widget' => 'single_text',
            ])
            ->add('especie', EntityType::class, [
                'label' => 'ejemplar.campo.especie',
                'class' => Especie::class,
                'choice_label' => 'nombre',
                'query_builder' => fn(EntityRepository $entityRepository): QueryBuilder => $entityRepository->createQueryBuilder('e')
                    ->orderBy('e.nombre', 'ASC'),
            ])
            ->add('idMicrochip', TextType::class, [
                'label' => 'ejemplar.campo.idMicrochip',
                'required' => false,
            ])
            ->add('idAnilla', TextType::class, [
                'label' => 'ejemplar.campo.idAnilla',
                'required' => false,
            ])
            ->add('idOtro', TextType::class, [
                'label' => 'ejemplar.campo.idOtro',
                'required' => false,
            ])
            ->add('idOtro2', TextType::class, [
                'label' => 'ejemplar.campo.idOtro2',
                'required' => false,
            ])
            ->add('sexo', ChoiceType::class, [
                'label' => 'ejemplar.campo.sexo',
                'choices' => array_flip(Constantes::$opciones_sexo),
            ])
            ->add('origen', ChoiceType::class, [
                'label' => 'ejemplar.campo.origen',
                'choices' => array_flip(Constantes::$opciones_origen),
            ])
            ->add('recinto', TextType::class, [
                'label' => 'ejemplar.campo.recinto',
                'required' => false,
            ])
            ->add('lugar', TextType::class, [
                'label' => 'ejemplar.campo.lugar',
                'required' => false,
            ])
            ->add('geoLat', NumberType::class, [
                'label' => 'ejemplar.campo.geoLat',
                'scale' => 12,
                'required' => false,
            ])
            ->add('geoLong', NumberType::class, [
                'label' => 'ejemplar.campo.geoLong',
                'scale' => 12,
                'required' => false,
            ])
            ->add('documentacion', ChoiceType::class, [
                'label' => 'ejemplar.campo.documentacion',
                'choices' => array_flip(Constantes::$opciones_documentacion),
            ])
            ->add('progenitor1', TextType::class, [
                'label' => 'ejemplar.campo.progenitor1',
                'required' => false,
            ])
            ->add('progenitor2', TextType::class, [
                'label' => 'ejemplar.campo.progenitor2',
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
            ->add('deposito', TextareaType::class, [
                'label' => 'ejemplar.campo.deposito',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'ejemplar.campo.observaciones',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('imagen1', FileType::class, [
                'label' => 'ejemplar.campo.imagen1',
                'required' => false,
                'mapped' => false,
            ])
            ->add('imagen2', FileType::class, [
                'label' => 'ejemplar.campo.imagen2',
                'required' => false,
                'mapped' => false,
            ])
            ->add('imagen3', FileType::class, [
                'label' => 'ejemplar.campo.imagen3',
                'required' => false,
                'mapped' => false,
            ])
            ->add('fechaBaja', DateType::class, [
                'label' => 'ejemplar.campo.fechaBaja',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('causaBaja', ChoiceType::class, [
                'label' => 'ejemplar.campo.causaBaja',
                'choices' => array_flip(Constantes::$causa_baja),
                'required' => false,
            ])
            ->add('enviar', SubmitType::class, [
                'label' => 'formulario.enviar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ejemplar::class,
        ]);
    }

    /**
     * Método específico que se puede sobrescribir en clases derivadas
     * para añadir campos adicionales (como checkboxes para borrar imágenes)
     */
    protected function especifico(FormBuilderInterface $formBuilder): void
    {
        // Por defecto no hace nada, se sobrescribe en EjemplarEditarType
    }
}
