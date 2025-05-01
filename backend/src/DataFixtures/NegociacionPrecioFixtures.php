<?php
namespace App\DataFixtures;

use App\Entity\NegociacionPrecio;
use App\Entity\Usuario;
use App\Entity\IntercambioObjeto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NegociacionPrecioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $negociaciones = [
            [
                'precio_propuesto' => 180,
                'mensaje' => '¿Podrías considerar este precio por la laptop?',
                'aceptado' => true,
                'usuario' => 'usuario_mariagarcia',
                'intercambio' => 'intercambio_objeto_laptop_hp_elitebook'
            ],
            [
                'precio_propuesto' => 120,
                'mensaje' => '¿Podemos negociar el precio del monitor?',
                'aceptado' => true,
                'usuario' => 'usuario_mariagarcia',
                'intercambio' => 'intercambio_objeto_monitor_lg_27'
            ]
        ];

        foreach ($negociaciones as $negociacionData) {
            try {
                $intercambio = $this->getReference($negociacionData['intercambio'], IntercambioObjeto::class);
                
                $negociacion = new NegociacionPrecio();
                $negociacion->setPrecioPropuesto($negociacionData['precio_propuesto']);
                $negociacion->setMensaje($negociacionData['mensaje']);
                $negociacion->setAceptado($negociacionData['aceptado']);
                $negociacion->setUsuario($this->getReference($negociacionData['usuario'], Usuario::class));
                $negociacion->setIntercambio($intercambio);
                $negociacion->setFechaCreacion(new \DateTimeImmutable());

                $manager->persist($negociacion);
            } catch (\Exception $e) {
                // Si el intercambio no existe, continuamos con el siguiente
                continue;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            IntercambioObjetoFixtures::class,
        ];
    }
}