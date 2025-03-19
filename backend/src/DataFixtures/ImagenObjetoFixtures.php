<?php
namespace App\DataFixtures;

use App\Entity\ImagenObjeto;
use App\Entity\Objeto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImagenObjetoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $imagenesObjetos = [
            ['objeto' => 0, 'url_imagen' => 'uploads/objetos/monitor1.jpg'],
            ['objeto' => 0, 'url_imagen' => 'uploads/objetos/monitor2.jpg'],
            ['objeto' => 1, 'url_imagen' => 'uploads/objetos/tablet1.jpg'],
            ['objeto' => 1, 'url_imagen' => 'uploads/objetos/tablet2.jpg'],
            ['objeto' => 2, 'url_imagen' => 'uploads/objetos/camara1.jpg'],
            ['objeto' => 2, 'url_imagen' => 'uploads/objetos/camara2.jpg'],
            ['objeto' => 3, 'url_imagen' => 'uploads/objetos/libros.jpg'],
            ['objeto' => 4, 'url_imagen' => 'uploads/objetos/herramientas1.jpg'],
            ['objeto' => 4, 'url_imagen' => 'uploads/objetos/herramientas2.jpg'],
            ['objeto' => 5, 'url_imagen' => 'uploads/objetos/cocina1.jpg'],
            ['objeto' => 5, 'url_imagen' => 'uploads/objetos/cocina2.jpg'],
        ];

        foreach ($imagenesObjetos as $imagenData) {
            $imagen = new ImagenObjeto();
            $imagen->setObjeto($this->getReference('objeto-' . $imagenData['objeto'], Objeto::class));
            $imagen->setUrlImagen($imagenData['url_imagen']);
            
            $manager->persist($imagen);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ObjetoFixtures::class,
        ];
    }
}