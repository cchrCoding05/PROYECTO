<?php
namespace App\DataFixtures;

use App\Entity\TransaccionCredito;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TransaccionCreditoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $transacciones = [
            [
                'usuario' => 'usuario-juanperez',
                'cantidad' => 50,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'usuario' => 'usuario-mariagonzalez',
                'cantidad' => -30,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'usuario' => 'usuario-pedrosan',
                'cantidad' => 75,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'usuario' => 'usuario-luciamartinez',
                'cantidad' => -45,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'usuario' => 'usuario-carlosrodriguez',
                'cantidad' => 60,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'usuario' => 'usuario-analopez',
                'cantidad' => -25,
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ]
        ];

        foreach ($transacciones as $transaccionData) {
            $transaccion = new TransaccionCredito();
            $transaccion->setUsuario($this->getReference($transaccionData['usuario'], Usuario::class));
            $transaccion->setCantidad($transaccionData['cantidad']);
            
            // Si necesitas establecer referencias a intercambios:
            // if ($transaccionData['intercambio_servicio']) {
            //     $transaccion->setIntercambioServicio($this->getReference($transaccionData['intercambio_servicio'], IntercambioServicio::class));
            // }
            
            // if ($transaccionData['intercambio_objeto']) {
            //     $transaccion->setIntercambioObjeto($this->getReference($transaccionData['intercambio_objeto'], IntercambioObjeto::class));
            // }
            
            $manager->persist($transaccion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            IntercambioServicioFixtures::class,
            IntercambioObjetoFixtures::class,
        ];
    }
}