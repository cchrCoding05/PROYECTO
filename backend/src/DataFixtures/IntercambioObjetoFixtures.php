<?php
namespace App\DataFixtures;

use App\Entity\IntercambioObjeto;
use App\Entity\Objeto;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntercambioObjetoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $intercambiosObjetos = [
            [
                'objeto' => 0,
                'vendedor' => 'juanperez',
                'comprador' => 'mariagonzalez',
                'creditos_propuestos' => 140,
                'fecha_solicitud' => new \DateTimeImmutable('-20 days'),
                'fecha_completado' => new \DateTimeImmutable('-15 days')
            ],
            [
                'objeto' => 1,
                'vendedor' => 'mariagonzalez',
                'comprador' => 'luciamartinez',
                'creditos_propuestos' => 190,
                'fecha_solicitud' => new \DateTimeImmutable('-18 days'),
                'fecha_completado' => new \DateTimeImmutable('-13 days')
            ],
            [
                'objeto' => 2,
                'vendedor' => 'pedrosan',
                'comprador' => 'carlosrodriguez',
                'creditos_propuestos' => 330,
                'fecha_solicitud' => new \DateTimeImmutable('-10 days'),
                'fecha_completado' => null
            ],
            [
                'objeto' => 3,
                'vendedor' => 'luciamartinez',
                'comprador' => 'analopez',
                'creditos_propuestos' => 110,
                'fecha_solicitud' => new \DateTimeImmutable('-7 days'),
                'fecha_completado' => null
            ]
        ];

        foreach ($intercambiosObjetos as $index => $intercambioData) {
            $intercambio = new IntercambioObjeto();
            $intercambio->setObjeto($this->getReference('objeto-' . $intercambioData['objeto'], Objeto::class));
            $intercambio->setVendedor($this->getReference('usuario-' . $intercambioData['vendedor'], Usuario::class));
            $intercambio->setComprador($this->getReference('usuario-' . $intercambioData['comprador'], Usuario::class));
            $intercambio->setCreditosPropuestos($intercambioData['creditos_propuestos']);
            $intercambio->setFechaSolicitud($intercambioData['fecha_solicitud']);
            
            if ($intercambioData['fecha_completado']) {
                $intercambio->setFechaCompletado($intercambioData['fecha_completado']);
            }
            
            $manager->persist($intercambio);
            
            // Referencias para usar en otras fixtures
            $this->addReference('intercambio-objeto-' . $index, $intercambio);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ObjetoFixtures::class,
            UsuarioFixtures::class,
        ];
    }
}