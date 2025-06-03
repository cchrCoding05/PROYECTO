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
            ],
            [
                'titulo' => 'Desarrollo de aplicaciones móviles',
                'descripcion' => 'Creación de apps nativas para iOS y Android',
                'creditos' => 60,
                'usuario' => 'usuario_sofia_tech',
                'categoria' => 'categoria_desarrollo_web'
            ],
            [
                'titulo' => 'Diseño de interfaces de usuario',
                'descripcion' => 'Diseño de UI/UX moderno y funcional',
                'creditos' => 45,
                'usuario' => 'usuario_pablo_design',
                'categoria' => 'categoria_diseno_grafico'
            ],
            [
                'titulo' => 'Gestión de redes sociales',
                'descripcion' => 'Administración y estrategia para redes sociales',
                'creditos' => 35,
                'usuario' => 'usuario_laura_marketing',
                'categoria' => 'categoria_marketing_digital'
            ],
            [
                'titulo' => 'Desarrollo backend con Node.js',
                'descripcion' => 'Creación de APIs y servicios backend robustos',
                'creditos' => 55,
                'usuario' => 'usuario_diego_dev',
                'categoria' => 'categoria_desarrollo_web'
            ],
            [
                'titulo' => 'Consultoría tecnológica',
                'descripcion' => 'Asesoramiento en transformación digital',
                'creditos' => 70,
                'usuario' => 'usuario_roberto_consultant',
                'categoria' => 'categoria_consultoria'
            ],
            [
                'titulo' => 'Clases de programación',
                'descripcion' => 'Tutorías personalizadas de programación',
                'creditos' => 30,
                'usuario' => 'usuario_elena_teacher',
                'categoria' => 'categoria_educacion'
            ],
            [
                'titulo' => 'Mentoría para startups',
                'descripcion' => 'Asesoramiento para emprendedores tecnológicos',
                'creditos' => 65,
                'usuario' => 'usuario_miguel_entrepreneur',
                'categoria' => 'categoria_consultoria'
            ],
            [
                'titulo' => 'Investigación de usuarios',
                'descripcion' => 'Estudios de usabilidad y experiencia de usuario',
                'creditos' => 50,
                'usuario' => 'usuario_patricia_ux',
                'categoria' => 'categoria_consultoria'
            ],
            [
                'titulo' => 'Desarrollo con React',
                'descripcion' => 'Creación de aplicaciones web con React',
                'creditos' => 45,
                'usuario' => 'usuario_sofia_tech',
                'categoria' => 'categoria_desarrollo_web'
            ],
            [
                'titulo' => 'Diseño de marca completa',
                'descripcion' => 'Desarrollo de identidad visual corporativa',
                'creditos' => 55,
                'usuario' => 'usuario_pablo_design',
                'categoria' => 'categoria_diseno_grafico'
            ],
            [
                'titulo' => 'SEO y posicionamiento',
                'descripcion' => 'Optimización para motores de búsqueda',
                'creditos' => 40,
                'usuario' => 'usuario_laura_marketing',
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