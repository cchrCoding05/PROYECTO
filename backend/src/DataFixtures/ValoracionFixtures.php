<?php
namespace App\DataFixtures;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Entity\IntercambioServicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ValoracionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $valoraciones = [
            // Valoraciones para el servicio de desarrollo web
            [
                'puntuacion' => 5,
                'comentario' => 'Excelente servicio, muy profesional y cumplido',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_desarrollo_de_sitios_web'
            ],
            [
                'puntuacion' => 4,
                'comentario' => 'Buen trabajo, aunque hubo algunos retrasos menores',
                'usuario' => 'usuario_juanperez',
                'intercambio_servicio' => 'intercambio_servicio_servicio_desarrollo_de_sitios_web'
            ],
            [
                'puntuacion' => 3,
                'comentario' => 'El servicio fue aceptable, pero podría mejorar en la comunicación',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_desarrollo_de_sitios_web'
            ],

            // Valoraciones para el servicio de diseño de logos
            [
                'puntuacion' => 2,
                'comentario' => 'No cumplió con las expectativas, el diseño fue muy básico',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_diseño_de_logos'
            ],
            [
                'puntuacion' => 5,
                'comentario' => '¡Increíble trabajo! El logo superó todas mis expectativas',
                'usuario' => 'usuario_juanperez',
                'intercambio_servicio' => 'intercambio_servicio_servicio_diseño_de_logos'
            ],

            // Valoraciones para el servicio de marketing digital
            [
                'puntuacion' => 1,
                'comentario' => 'Muy decepcionante, no se cumplieron los objetivos acordados',
                'usuario' => 'usuario_juanperez',
                'intercambio_servicio' => 'intercambio_servicio_servicio_estrategia_de_marketing_digital'
            ],
            [
                'puntuacion' => 5,
                'comentario' => 'Estrategia de marketing muy efectiva, superó nuestras expectativas',
                'usuario' => 'usuario_mariagarcia',
                'intercambio_servicio' => 'intercambio_servicio_servicio_estrategia_de_marketing_digital'
            ],
            [
                'puntuacion' => 4,
                'comentario' => 'Buen trabajo en general, aunque el ROI podría ser mejor',
                'usuario' => 'usuario_juanperez',
                'intercambio_servicio' => 'intercambio_servicio_servicio_estrategia_de_marketing_digital'
            ]
        ];

        foreach ($valoraciones as $valoracionData) {
            $valoracion = new Valoracion();
            $valoracion->setPuntuacion($valoracionData['puntuacion']);
            $valoracion->setComentario($valoracionData['comentario']);
            $valoracion->setUsuario($this->getReference($valoracionData['usuario'], Usuario::class));
            $valoracion->setIntercambioServicio($this->getReference($valoracionData['intercambio_servicio'], IntercambioServicio::class));
            $valoracion->setFechaCreacion(new \DateTimeImmutable());

            $manager->persist($valoracion);
        }

        $manager->flush();

        // Crear referencias después de tener los IDs
        $valoracionesGuardadas = $manager->getRepository(Valoracion::class)->findAll();
        foreach ($valoracionesGuardadas as $valoracion) {
            $this->addReference('valoracion-' . $valoracion->getId_valoracion(), $valoracion);
        }
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            IntercambioServicioFixtures::class,
        ];
    }
}