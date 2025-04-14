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
            'Programación', 'Diseño', 'Reparación', 'Clases', 'Tutoría', 'Consultoría',
            'Limpieza', 'Jardinería', 'Pintura', 'Fontanería', 'Electricidad', 
            'Traducciones', 'Fotografía', 'Cuidado', 'Cocina', 'Música', 'Masajes',
            'Legal', 'Financiero', 'Marketing', 'Social Media', 'Desarrollo web',
            'Carpintería', 'Transporte', 'Mudanzas', 'Idiomas', 'Deporte', 'Eventos'
        ];

        foreach ($etiquetas as $nombreEtiqueta) {
            $etiqueta = new Etiqueta();
            $etiqueta->setNombre($nombreEtiqueta);
            
            $manager->persist($etiqueta);
            
            // Referencias para usar en otras fixtures
            $this->addReference('etiqueta-' . $nombreEtiqueta, $etiqueta);
        }

        $manager->flush();
    }
}