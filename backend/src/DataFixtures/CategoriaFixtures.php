<?php
namespace App\DataFixtures;

use App\Entity\Categoria;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoriaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categorias = [
            ['nombre' => 'Informática', 'descripcion' => 'Servicios relacionados con programación, reparación de ordenadores, etc.'],
            ['nombre' => 'Educación', 'descripcion' => 'Clases particulares, tutorías, asesoramiento educativo'],
            ['nombre' => 'Hogar', 'descripcion' => 'Servicios domésticos, reparaciones, jardinería, etc.'],
            ['nombre' => 'Salud', 'descripcion' => 'Servicios relacionados con bienestar, fisioterapia, nutrición, etc.'],
            ['nombre' => 'Arte', 'descripcion' => 'Servicios artísticos como pintura, música, fotografía, etc.'],
            ['nombre' => 'Legal', 'descripcion' => 'Asesoramiento legal, trámites, consultas, etc.'],
            ['nombre' => 'Transporte', 'descripcion' => 'Servicios de transporte, mudanzas, mensajería, etc.'],
            ['nombre' => 'Eventos', 'descripcion' => 'Organización de eventos, animación, catering, etc.']
        ];

        foreach ($categorias as $categoriaData) {
            $categoria = new Categoria();
            $categoria->setNombre($categoriaData['nombre']);
            $categoria->setDescripcion($categoriaData['descripcion']);
            
            $manager->persist($categoria);
            
            // Referencias para usar en otras fixtures
            $this->addReference('categoria-' . $categoriaData['nombre'], $categoria);
        }

        $manager->flush();
    }
}