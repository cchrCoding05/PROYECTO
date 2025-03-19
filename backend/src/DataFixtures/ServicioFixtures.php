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
                'usuario' => 'juanperez', 
                'titulo' => 'Desarrollo de sitios web', 
                'descripcion' => 'Creación de sitios web modernos y responsivos con las últimas tecnologías', 
                'categoria' => 'Informática', 
                'creditos' => 80,
                'etiquetas' => ['Programación', 'Desarrollo web']
            ],
            [
                'usuario' => 'juanperez', 
                'titulo' => 'Clases de programación', 
                'descripcion' => 'Clases particulares de programación para principiantes', 
                'categoria' => 'Educación', 
                'creditos' => 40,
                'etiquetas' => ['Programación', 'Clases', 'Tutoría']
            ],
            [
                'usuario' => 'mariagonzalez', 
                'titulo' => 'Diseño de logotipos', 
                'descripcion' => 'Diseño profesional de logotipos para empresas y particulares', 
                'categoria' => 'Arte', 
                'creditos' => 60,
                'etiquetas' => ['Diseño']
            ],
            [
                'usuario' => 'pedrosan', 
                'titulo' => 'Sesiones fotográficas', 
                'descripcion' => 'Sesiones fotográficas para eventos, retratos, productos, etc.', 
                'categoria' => 'Arte', 
                'creditos' => 70,
                'etiquetas' => ['Fotografía']
            ],
            [
                'usuario' => 'luciamartinez', 
                'titulo' => 'Clases de matemáticas', 
                'descripcion' => 'Clases particulares de matemáticas para todos los niveles', 
                'categoria' => 'Educación', 
                'creditos' => 35,
                'etiquetas' => ['Clases', 'Tutoría']
            ],
            [
                'usuario' => 'carlosrodriguez', 
                'titulo' => 'Reparaciones eléctricas', 
                'descripcion' => 'Reparación de instalaciones eléctricas en hogares', 
                'categoria' => 'Hogar', 
                'creditos' => 55,
                'etiquetas' => ['Reparación', 'Electricidad']
            ],
            [
                'usuario' => 'analopez', 
                'titulo' => 'Catering para eventos', 
                'descripcion' => 'Servicio de catering para eventos y celebraciones', 
                'categoria' => 'Eventos', 
                'creditos' => 90,
                'etiquetas' => ['Cocina', 'Eventos']
            ]
        ];

        foreach ($servicios as $index => $servicioData) {
            $servicio = new Servicio();
            $servicio->setUsuario($this->getReference('usuario-' . $servicioData['usuario'], Usuario::class));
            $servicio->setTitulo($servicioData['titulo']);
            $servicio->setDescripcion($servicioData['descripcion']);
            $servicio->setCategoria($this->getReference('categoria-' . $servicioData['categoria'], Categoria::class));
            $servicio->setCreditos($servicioData['creditos']);
            $servicio->setActivo(true);

            // Añadir etiquetas
// Añadir etiquetas

foreach ($servicioData['etiquetas'] as $etiquetaNombre) {
    $servicio->addEtiqueta($this->getReference('etiqueta-' . $etiquetaNombre, Etiqueta::class));
}

            
            $manager->persist($servicio);
            
            // Referencias para usar en otras fixtures
            $this->addReference('servicio-' . $index, $servicio);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            CategoriaFixtures::class,
            EtiquetaFixtures::class,
        ];
    }
}