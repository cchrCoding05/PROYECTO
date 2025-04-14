<?php

namespace App\Form;

use App\Entity\IntercambioObjeto;
use App\Entity\IntercambioServicio;
use App\Entity\Usuario;
use App\Entity\Valoracion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Valoracion1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('puntuacion')
            ->add('comentario')
            ->add('fecha_valoracion', null, [
                'widget' => 'single_text',
            ])
            ->add('intercambio_servicio', EntityType::class, [
                'class' => IntercambioServicio::class,
                'choice_label' => 'id',
            ])
            ->add('intercambio_objeto', EntityType::class, [
                'class' => IntercambioObjeto::class,
                'choice_label' => 'id',
            ])
            ->add('evaluador', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
            ->add('evaluado', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Valoracion::class,
        ]);
    }
}
