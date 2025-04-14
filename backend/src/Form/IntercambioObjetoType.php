<?php

namespace App\Form;

use App\Entity\IntercambioObjeto;
use App\Entity\Objeto;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntercambioObjetoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('creditos_propuestos')
            ->add('fecha_solicitud', null, [
                'widget' => 'single_text',
            ])
            ->add('fecha_completado', null, [
                'widget' => 'single_text',
            ])
            ->add('objeto', EntityType::class, [
                'class' => Objeto::class,
                'choice_label' => 'id',
            ])
            ->add('vendedor', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
            ->add('comprador', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IntercambioObjeto::class,
        ]);
    }
}
