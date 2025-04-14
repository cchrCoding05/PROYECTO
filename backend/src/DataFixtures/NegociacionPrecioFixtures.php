<?php
namespace App\DataFixtures;

use App\Entity\IntercambioObjeto;
use App\Entity\Usuario;
use App\Entity\NegociacionPrecio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NegociacionPrecioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $negociaciones = [
            [
                'intercambio' => 0,
                'usuario' => 'mariagonzalez',
                'creditos_propuestos' => 130,
                'mensaje' => '¿Podrías bajar un poco el precio? Ofrezco 130 créditos.',
                'fecha_negociacion' => new \DateTimeImmutable('-19 days')
            ],
            [
                'intercambio' => 0,
                'usuario' => 'juanperez',
                'creditos_propuestos' => 140,
                'mensaje' => 'Te puedo bajar a 140 créditos, es mi mejor oferta.',
                'fecha_negociacion' => new \DateTimeImmutable('-18 days')
            ],
            [
                'intercambio' => 1,
                'usuario' => 'luciamartinez',
                'creditos_propuestos' => 180,
                'mensaje' => '¿Aceptarías 180 créditos?',
                'fecha_negociacion' => new \DateTimeImmutable('-17 days')
            ],
            [
                'intercambio' => 1,
                'usuario' => 'mariagonzalez',
                'creditos_propuestos' => 190,
                'mensaje' => 'Te ofrezco 190 créditos, incluye la funda protectora.',
                'fecha_negociacion' => new \DateTimeImmutable('-16 days')
            ],
            [
                'intercambio' => 2,
                'usuario' => 'carlosrodriguez',
                'creditos_propuestos' => 300,
                'mensaje' => 'Me interesa mucho, pero mi presupuesto es de 300 créditos.',
                'fecha_negociacion' => new \DateTimeImmutable('-9 days')
            ],
            [
                'intercambio' => 2,
                'usuario' => 'pedrosan',
                'creditos_propuestos' => 330,
                'mensaje' => 'Puedo aceptar 330 créditos si te la llevas esta semana.',
                'fecha_negociacion' => new \DateTimeImmutable('-8 days')
            ]
        ];

        foreach ($negociaciones as $negociacionData) {
            $negociacion = new NegociacionPrecio();
            $negociacion->setIntercambio($this->getReference('intercambio-objeto-' . $negociacionData['intercambio'], IntercambioObjeto::class));
            $negociacion->setUsuario($this->getReference('usuario-' . $negociacionData['usuario'], Usuario::class));
            $negociacion->setCreditosPropuestos($negociacionData['creditos_propuestos']);
            $negociacion->setMensaje($negociacionData['mensaje']);
            $negociacion->setFechaNegociacion($negociacionData['fecha_negociacion']);
            
            $manager->persist($negociacion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            IntercambioObjetoFixtures::class,
            UsuarioFixtures::class,
        ];
    }
}