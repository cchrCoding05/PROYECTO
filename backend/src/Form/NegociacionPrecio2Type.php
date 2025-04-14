<?php

namespace App\Form;

use App\Entity\IntercambioObjeto;
use App\Entity\NegociacionPrecio;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NegociacionPrecio2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('creditos_propuestos')
            ->add('mensaje')
            ->add('fecha_negociacion', null, [
                'widget' => 'single_text',
            ])
            ->add('intercambio', EntityType::class, [
                'class' => IntercambioObjeto::class,
                'choice_label' => 'id',
            ])
            ->add('usuario', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NegociacionPrecio::class,
        ]);
    }
}
