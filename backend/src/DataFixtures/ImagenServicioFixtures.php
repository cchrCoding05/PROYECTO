<?php
namespace App\DataFixtures;

use App\Entity\ImagenServicio;
use App\Entity\Servicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImagenServicioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $imagenesServicios = [
            ['servicio' => 0, 'url_imagen' => 'uploads/servicios/desarrollo-web1.jpg'],
            ['servicio' => 0, 'url_imagen' => 'uploads/servicios/desarrollo-web2.jpg'],
            ['servicio' => 1, 'url_imagen' => 'uploads/servicios/clases-programacion.jpg'],
            ['servicio' => 2, 'url_imagen' => 'uploads/servicios/diseno-logo1.jpg'],
            ['servicio' => 2, 'url_imagen' => 'uploads/servicios/diseno-logo2.jpg'],
            ['servicio' => 3, 'url_imagen' => 'uploads/servicios/fotografia1.jpg'],
            ['servicio' => 3, 'url_imagen' => 'uploads/servicios/fotografia2.jpg'],
            ['servicio' => 4, 'url_imagen' => 'uploads/servicios/matematicas.jpg'],
            ['servicio' => 5, 'url_imagen' => 'uploads/servicios/electricidad.jpg'],
            ['servicio' => 6, 'url_imagen' => 'uploads/servicios/catering1.jpg'],
            ['servicio' => 6, 'url_imagen' => 'uploads/servicios/catering2.jpg'],
        ];

        foreach ($imagenesServicios as $imagenData) {
            $imagen = new ImagenServicio();
            $imagen->setServicio($this->getReference('servicio-' . $imagenData['servicio'], Servicio::class));
            $imagen->setUrlImagen($imagenData['url_imagen']);
            
            $manager->persist($imagen);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ServicioFixtures::class,
        ];
    }
}