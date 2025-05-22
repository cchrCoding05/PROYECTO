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
                'descripcion' => 'Servicios relacionados con el desarrollo de sitios web y aplicaciones web',
                'referencia' => 'categoria_desarrollo_web'
            ],
            [
                'nombre' => 'Diseño Gráfico',
                'descripcion' => 'Servicios de diseño gráfico, branding y diseño de interfaces',
                'referencia' => 'categoria_diseno_grafico'
            ],
            [
                'nombre' => 'Marketing Digital',
                'descripcion' => 'Servicios de marketing digital, SEO y gestión de redes sociales',
                'referencia' => 'categoria_marketing_digital'
            ],
            [
                'nombre' => 'Consultoría',
                'descripcion' => 'Servicios de consultoría tecnológica y estratégica',
                'referencia' => 'categoria_consultoria'
            ],
            [
                'nombre' => 'Educación',
                'descripcion' => 'Servicios educativos y de formación',
                'referencia' => 'categoria_educacion'
            ]
        ];

        foreach ($categorias as $categoriaData) {
            $categoria = new Categoria();
            $categoria->setNombre($categoriaData['nombre']);
            $categoria->setDescripcion($categoriaData['descripcion']);
            
            $manager->persist($categoria);
            $this->addReference($categoriaData['referencia'], $categoria);
        }

        $manager->flush();
    }
}