<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Usuario1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre_usuario')
            ->add('correo')
            ->add('contrasena')
            ->add('foto_perfil')
            ->add('descripcion')
            ->add('profesion')
            ->add('fecha_registro', null, [
                'widget' => 'single_text',
            ])
            ->add('creditos')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
