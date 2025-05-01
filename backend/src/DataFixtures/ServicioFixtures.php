<?php
namespace App\DataFixtures;

use App\Entity\Servicio;
use App\Entity\Usuario;
use App\Entity\Categoria;
use App\Entity\Etiqueta;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ServicioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $servicios = [
            [
                'titulo' => 'Desarrollo de sitios web',
                'descripcion' => 'Desarrollo de sitios web personalizados con las últimas tecnologías',
                'creditos' => 50,
                'usuario' => 'usuario_juanperez',
                'categoria' => 'categoria_desarrollo_web'
            ],
            [
                'titulo' => 'Diseño de logos',
                'descripcion' => 'Creación de logos profesionales y branding',
                'creditos' => 30,
                'usuario' => 'usuario_mariagarcia',
                'categoria' => 'categoria_diseno_grafico'
            ],
            [
                'titulo' => 'Estrategia de marketing digital',
                'descripcion' => 'Desarrollo de estrategias de marketing digital efectivas',
                'creditos' => 40,
                'usuario' => 'usuario_carloslopez',
                'categoria' => 'categoria_marketing_digital'
            ]
        ];

        foreach ($servicios as $servicioData) {
            $servicio = new Servicio();
            $servicio->setTitulo($servicioData['titulo']);
            $servicio->setDescripcion($servicioData['descripcion']);
            $servicio->setCreditos($servicioData['creditos']);
            $servicio->setUsuario($this->getReference($servicioData['usuario'], Usuario::class));
            $servicio->setCategoria($this->getReference($servicioData['categoria'], Categoria::class));

            $manager->persist($servicio);
            $this->addReference('servicio_' . strtolower(str_replace(' ', '_', $servicioData['titulo'])), $servicio);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            CategoriaFixtures::class,
        ];
    }
}