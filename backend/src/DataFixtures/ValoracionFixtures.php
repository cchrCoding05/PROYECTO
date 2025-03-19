<?php
namespace App\DataFixtures;

use App\Entity\Valoracion;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ValoracionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $valoraciones = [
            [
                'evaluador' => 'usuario-juanperez',
                'evaluado' => 'usuario-mariagonzalez',
                'puntuacion' => 5,
                'comentario' => 'Excelente trabajo de diseño, muy profesional y puntual.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'evaluador' => 'usuario-mariagonzalez',
                'evaluado' => 'usuario-juanperez',
                'puntuacion' => 4,
                'comentario' => 'Buen programador, resolvió todos los problemas que tenía.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'evaluador' => 'usuario-pedrosan',
                'evaluado' => 'usuario-luciamartinez',
                'puntuacion' => 5,
                'comentario' => 'Excelente profesora, muy clara explicando.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'evaluador' => 'usuario-luciamartinez',
                'evaluado' => 'usuario-carlosrodriguez',
                'puntuacion' => 3,
                'comentario' => 'Hizo el trabajo correctamente pero tardó más de lo acordado.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'evaluador' => 'usuario-carlosrodriguez',
                'evaluado' => 'usuario-analopez',
                'puntuacion' => 5,
                'comentario' => 'Las clases de cocina fueron increíbles, aprendí mucho.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ],
            [
                'evaluador' => 'usuario-analopez',
                'evaluado' => 'usuario-juanperez',
                'puntuacion' => 4,
                'comentario' => 'Solucionó el problema de mi web rápidamente.',
                'intercambio_servicio' => null,
                'intercambio_objeto' => null
            ]
        ];

        foreach ($valoraciones as $valoracionData) {
            $valoracion = new Valoracion();
            $valoracion->setEvaluador($this->getReference($valoracionData['evaluador'], Usuario::class));
            $valoracion->setEvaluado($this->getReference($valoracionData['evaluado'], Usuario::class));
            $valoracion->setPuntuacion($valoracionData['puntuacion']);
            $valoracion->setComentario($valoracionData['comentario']);
            
            // Para los intercambios necesitarías referencias a esos objetos
            // Por ahora los dejaremos como null, pero si tienes fixtures para intercambios,
            // podrías establecerlos aquí mediante referencias
            
            // La fecha de valoración se establece automáticamente en el constructor
            
            $manager->persist($valoracion);
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