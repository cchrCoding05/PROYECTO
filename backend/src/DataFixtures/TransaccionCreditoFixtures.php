<?php
namespace App\DataFixtures;

use App\Entity\TransaccionCredito;
use App\Entity\Usuario;
use App\Entity\IntercambioServicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TransaccionCreditoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $transacciones = [
            [
                'cantidad' => 50,
                'tipo' => 'compra',
                'descripcion' => 'Compra de servicio de desarrollo web',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_desarrollo_de_sitios_web'
            ],
            [
                'cantidad' => 30,
                'tipo' => 'compra',
                'descripcion' => 'Compra de servicio de diseño de logo',
                'usuario' => 'usuario_juanperez',
                'intercambio_servicio' => 'intercambio_servicio_servicio_diseño_de_logos'
            ],
            [
                'cantidad' => 40,
                'tipo' => 'compra',
                'descripcion' => 'Compra de servicio de marketing digital',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_estrategia_de_marketing_digital'
            ]
        ];

        foreach ($transacciones as $transaccionData) {
            $transaccion = new TransaccionCredito();
            $transaccion->setCantidad($transaccionData['cantidad']);
            $transaccion->setTipo($transaccionData['tipo']);
            $transaccion->setDescripcion($transaccionData['descripcion']);
            $transaccion->setUsuario($this->getReference($transaccionData['usuario'], Usuario::class));
            $transaccion->setIntercambioServicio($this->getReference($transaccionData['intercambio_servicio'], IntercambioServicio::class));
            $transaccion->setFechaCreacion(new \DateTimeImmutable());

            $manager->persist($transaccion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            IntercambioServicioFixtures::class,
        ];
    }
}