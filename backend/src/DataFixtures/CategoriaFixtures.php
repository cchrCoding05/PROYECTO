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
            [
                'nombre' => 'Desarrollo Web',
                'descripcion' => 'Servicios relacionados con el desarrollo de sitios web y aplicaciones web'
            ],
            [
                'nombre' => 'Diseno Grafico',
                'descripcion' => 'Servicios de diseño visual, branding y creatividad'
            ],
            [
                'nombre' => 'Marketing Digital',
                'descripcion' => 'Servicios de marketing online, SEO y redes sociales'
            ],
            [
                'nombre' => 'Consultoría',
                'descripcion' => 'Servicios de asesoramiento y consultoría profesional'
            ],
            [
                'nombre' => 'Educación',
                'descripcion' => 'Servicios de enseñanza y formación'
            ]
        ];

        foreach ($categorias as $categoriaData) {
            $categoria = new Categoria();
            $categoria->setNombre($categoriaData['nombre']);
            $categoria->setDescripcion($categoriaData['descripcion']);

            $manager->persist($categoria);
            $this->addReference('categoria_' . strtolower(str_replace(' ', '_', $categoriaData['nombre'])), $categoria);
        }

        $manager->flush();
    }
}