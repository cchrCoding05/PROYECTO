<?php
namespace App\DataFixtures;

use App\Entity\NegociacionPrecio;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NegociacionPrecioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Obtener usuarios para crear negociaciones
        $usuarios = $manager->getRepository(Usuario::class)->findAll();
        
        // Crear algunas negociaciones de ejemplo
        for ($i = 0; $i < 5; $i++) {
            $negociacion = new NegociacionPrecio();
            
            // Seleccionar comprador y vendedor aleatorios
            $comprador = $usuarios[array_rand($usuarios)];
            $vendedor = $usuarios[array_rand($usuarios)];
            
            // Asegurarse de que no sean el mismo usuario
            while ($vendedor === $comprador) {
                $vendedor = $usuarios[array_rand($usuarios)];
            }
            
            $negociacion->setComprador($comprador);
            $negociacion->setVendedor($vendedor);
            $negociacion->setPrecioPropuesto(rand(50, 500));
            $negociacion->setAceptado(false);

                $manager->persist($negociacion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
        ];
    }
}