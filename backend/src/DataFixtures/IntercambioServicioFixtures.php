<?php
namespace App\DataFixtures;

use App\Entity\IntercambioServicio;
use App\Entity\Usuario;
use App\Entity\Servicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntercambioServicioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $intercambios = [
            [
                'cantidad_creditos' => 50,
                'estado' => 'completado',
                'servicio' => 'servicio_desarrollo_de_sitios_web',
                'solicitante' => 'usuario_mariagarcia',
                'fecha_completado' => new \DateTimeImmutable('2024-03-15')
            ],
            [
                'cantidad_creditos' => 30,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_diseño_de_logos',
                'solicitante' => 'usuario_juanperez',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 40,
                'estado' => 'completado',
                'servicio' => 'servicio_estrategia_de_marketing_digital',
                'solicitante' => 'usuario_mariagarcia',
                'fecha_completado' => new \DateTimeImmutable('2024-03-20')
            ]
        ];

        foreach ($intercambios as $intercambioData) {
            $intercambio = new IntercambioServicio();
            $intercambio->setCantidadCreditos($intercambioData['cantidad_creditos']);
            $intercambio->setEstado($intercambioData['estado']);
            $intercambio->setServicio($this->getReference($intercambioData['servicio'], Servicio::class));
            $intercambio->setSolicitante($this->getReference($intercambioData['solicitante'], Usuario::class));
            $intercambio->setFechaSolicitud(new \DateTimeImmutable());
            $intercambio->setFechaCompletado($intercambioData['fecha_completado']);

            $manager->persist($intercambio);
            $this->addReference('intercambio_servicio_' . $intercambioData['servicio'], $intercambio);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            ServicioFixtures::class,
        ];
    }
}