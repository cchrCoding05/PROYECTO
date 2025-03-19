<?php
namespace App\DataFixtures;

use App\Entity\Objeto;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ObjetoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $objetos = [
            [
                'usuario' => 'juanperez',
                'titulo' => 'Monitor 24" Full HD',
                'descripcion' => 'Monitor en excelente estado, apenas un año de uso. Resolución Full HD.',
                'creditos' => 150
            ],
            [
                'usuario' => 'mariagonzalez',
                'titulo' => 'Tablet Samsung Galaxy Tab A',
                'descripcion' => 'Tablet en buen estado, con funda protectora incluida. 64GB de almacenamiento.',
                'creditos' => 200
            ],
            [
                'usuario' => 'pedrosan',
                'titulo' => 'Cámara Canon EOS 700D',
                'descripcion' => 'Cámara réflex en buen estado. Incluye objetivo 18-55mm y batería extra.',
                'creditos' => 350
            ],
            [
                'usuario' => 'luciamartinez',
                'titulo' => 'Colección de libros de matemáticas',
                'descripcion' => 'Colección de 10 libros de matemáticas nivel universitario. Como nuevos.',
                'creditos' => 120
            ],
            [
                'usuario' => 'carlosrodriguez',
                'titulo' => 'Set de herramientas eléctricas',
                'descripcion' => 'Set completo de herramientas para electricista. Poco uso.',
                'creditos' => 180
            ],
            [
                'usuario' => 'analopez',
                'titulo' => 'Batería de cocina premium',
                'descripcion' => 'Juego de ollas y sartenes de alta calidad. Aptas para todo tipo de cocinas.',
                'creditos' => 250
            ]
        ];

        foreach ($objetos as $index => $objetoData) {
            $objeto = new Objeto();
            $objeto->setUsuario($this->getReference('usuario-' . $objetoData['usuario'], Usuario::class));
            $objeto->setTitulo($objetoData['titulo']);
            $objeto->setDescripcion($objetoData['descripcion']);
            $objeto->setCreditos($objetoData['creditos']);
            
            $manager->persist($objeto);
            
            // Referencias para usar en otras fixtures
            $this->addReference('objeto-' . $index, $objeto);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
        ];
    }
}