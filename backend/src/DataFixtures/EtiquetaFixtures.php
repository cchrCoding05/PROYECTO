<?php
namespace App\DataFixtures;

use App\Entity\Etiqueta;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtiquetaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etiquetas = [
            'PHP',
            'JavaScript',
            'HTML',
            'CSS',
            'Diseño',
            'Marketing',
            'SEO',
            'Redes Sociales',
            'Branding',
            'Consultoría'
        ];

        foreach ($etiquetas as $nombre) {
            $etiqueta = new Etiqueta();
            $etiqueta->setNombre($nombre);

            $manager->persist($etiqueta);
            $this->addReference('etiqueta_' . strtolower($nombre), $etiqueta);
        }

        $manager->flush();
    }
}