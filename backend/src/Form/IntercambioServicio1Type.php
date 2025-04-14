<?php

namespace App\Form;

use App\Entity\IntercambioServicio;
use App\Entity\Servicio;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntercambioServicio1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad_creditos')
            ->add('fecha_solicitud', null, [
                'widget' => 'single_text',
            ])
            ->add('fecha_completado', null, [
                'widget' => 'single_text',
            ])
            ->add('servicio', EntityType::class, [
                'class' => Servicio::class,
                'choice_label' => 'id',
            ])
            ->add('solicitante', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IntercambioServicio::class,
        ]);
    }
}
