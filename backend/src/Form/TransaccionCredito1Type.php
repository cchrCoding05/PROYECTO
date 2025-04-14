<?php

namespace App\Form;

use App\Entity\IntercambioObjeto;
use App\Entity\IntercambioServicio;
use App\Entity\TransaccionCredito;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransaccionCredito1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad')
            ->add('fecha_transaccion', null, [
                'widget' => 'single_text',
            ])
            ->add('usuario', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
            ])
            ->add('intercambio_servicio', EntityType::class, [
                'class' => IntercambioServicio::class,
                'choice_label' => 'id',
            ])
            ->add('intercambio_objeto', EntityType::class, [
                'class' => IntercambioObjeto::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransaccionCredito::class,
        ]);
    }
}
