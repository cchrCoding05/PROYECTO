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
            ],
            [
                'cantidad_creditos' => 60,
                'estado' => 'completado',
                'servicio' => 'servicio_desarrollo_de_aplicaciones_móviles',
                'solicitante' => 'usuario_pablo_design',
                'fecha_completado' => new \DateTimeImmutable('2024-03-25')
            ],
            [
                'cantidad_creditos' => 45,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_diseño_de_interfaces_de_usuario',
                'solicitante' => 'usuario_sofia_tech',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 35,
                'estado' => 'completado',
                'servicio' => 'servicio_gestión_de_redes_sociales',
                'solicitante' => 'usuario_diego_dev',
                'fecha_completado' => new \DateTimeImmutable('2024-03-28')
            ],
            [
                'cantidad_creditos' => 55,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_desarrollo_backend_con_node.js',
                'solicitante' => 'usuario_laura_marketing',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 40,
                'estado' => 'completado',
                'servicio' => 'servicio_ilustración_digital',
                'solicitante' => 'usuario_roberto_consultant',
                'fecha_completado' => new \DateTimeImmutable('2024-03-30')
            ],
            [
                'cantidad_creditos' => 70,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_consultoría_tecnológica',
                'solicitante' => 'usuario_carmen_art',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 30,
                'estado' => 'completado',
                'servicio' => 'servicio_clases_de_programación',
                'solicitante' => 'usuario_miguel_entrepreneur',
                'fecha_completado' => new \DateTimeImmutable('2024-04-01')
            ],
            [
                'cantidad_creditos' => 65,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_mentoría_para_startups',
                'solicitante' => 'usuario_patricia_ux',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 50,
                'estado' => 'completado',
                'servicio' => 'servicio_investigación_de_usuarios',
                'solicitante' => 'usuario_elena_teacher',
                'fecha_completado' => new \DateTimeImmutable('2024-04-05')
            ],
            [
                'cantidad_creditos' => 45,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_desarrollo_con_react',
                'solicitante' => 'usuario_sofia_tech',
                'fecha_completado' => null
            ],
            [
                'cantidad_creditos' => 55,
                'estado' => 'completado',
                'servicio' => 'servicio_diseño_de_marca_completa',
                'solicitante' => 'usuario_pablo_design',
                'fecha_completado' => new \DateTimeImmutable('2024-04-10')
            ],
            [
                'cantidad_creditos' => 40,
                'estado' => 'en_proceso',
                'servicio' => 'servicio_seo_y_posicionamiento',
                'solicitante' => 'usuario_laura_marketing',
                'fecha_completado' => null
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