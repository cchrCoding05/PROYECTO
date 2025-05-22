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
            // Objetos de tecnología
            [
                'titulo' => 'Laptop HP EliteBook',
                'descripcion' => 'Laptop en excelente estado, ideal para trabajo y estudio',
                'creditos' => 360,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519023/huesjd27kvomjhmi3lnp.png'
            ],
            [
                'titulo' => 'Toyota A86 "Trueno"',
                'descripcion' => 'Coche de culto en los cómics, elección de leyendas del drifting y un reputado tracción trasera',
                'creditos' => 22000,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746693003/sld3knlzq6fmf6vkikk9.png'
            ],
            [
                'titulo' => 'Teclado Mecánico RGB',
                'descripcion' => 'Teclado mecánico RGB con switches Cherry MX Red',
                'creditos' => 250,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746518852/j6ikhhp4sfsdpa080kwe.png'
            ],
            [
                'titulo' => 'Auriculares Sony WH-1000XM4',
                'descripcion' => 'Auriculares inalámbricos con cancelación de ruido',
                'creditos' => 180,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519081/znybpgsmkb7wmdibtrrq.png'
            ],
            [
                'titulo' => 'Cámara Canon EOS R5',
                'descripcion' => 'Cámara profesional con lente 24-70mm, perfecta para fotografía',
                'creditos' => 600,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_RESERVADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519153/kdhdrzntstgg2karnnx6.png'
            ],
            [
                'titulo' => 'Monitor LG UltraGear 27"',
                'descripcion' => 'Monitor gaming 4K con excelente calidad de imagen',
                'creditos' => 420,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_RESERVADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519178/i5xuqnltkmscwv3ihwae.png'
            ],
            [
                'titulo' => 'Smartphone Samsung S21',
                'descripcion' => 'Smartphone en perfecto estado, con todos sus accesorios',
                'creditos' => 350,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_INTERCAMBIADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519198/dsvjc3hzovw5egy6w4nr.png'
            ],
            [
                'titulo' => 'iPad Pro 12.9"',
                'descripcion' => 'Tablet Apple con lápiz digital incluido',
                'creditos' => 400,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_INTERCAMBIADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519235/igkyggrvdu4ntg8jtrgq.png'
            ],
            // Nuevos objetos
            [
                'titulo' => 'MacBook Pro M1',
                'descripcion' => 'Laptop Apple con chip M1, 16GB RAM, 512GB SSD',
                'creditos' => 800,
                'usuario' => 'usuario_sofia_tech',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519023/huesjd27kvomjhmi3lnp.png'
            ],
            [
                'titulo' => 'Wacom Cintiq 22',
                'descripcion' => 'Tableta gráfica profesional para diseño digital',
                'creditos' => 450,
                'usuario' => 'usuario_pablo_design',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746518852/j6ikhhp4sfsdpa080kwe.png'
            ],
            [
                'titulo' => 'DJI Mavic Air 2',
                'descripcion' => 'Drone profesional con cámara 4K',
                'creditos' => 550,
                'usuario' => 'usuario_laura_marketing',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519081/znybpgsmkb7wmdibtrrq.png'
            ],
            [
                'titulo' => 'PlayStation 5',
                'descripcion' => 'Consola PS5 con dos mandos y juegos incluidos',
                'creditos' => 480,
                'usuario' => 'usuario_diego_dev',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519153/kdhdrzntstgg2karnnx6.png'
            ],
            [
                'titulo' => 'iPad Pro 11"',
                'descripcion' => 'Tablet Apple con lápiz digital y teclado',
                'creditos' => 380,
                'usuario' => 'usuario_carmen_art',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519178/i5xuqnltkmscwv3ihwae.png'
            ],
            [
                'titulo' => 'Microsoft Surface Studio',
                'descripcion' => 'Monitor táctil profesional para diseño',
                'creditos' => 1200,
                'usuario' => 'usuario_roberto_consultant',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519198/dsvjc3hzovw5egy6w4nr.png'
            ],
            [
                'titulo' => 'Raspberry Pi 4 Kit',
                'descripcion' => 'Kit completo para proyectos de programación',
                'creditos' => 150,
                'usuario' => 'usuario_elena_teacher',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519235/igkyggrvdu4ntg8jtrgq.png'
            ],
            [
                'titulo' => 'Oculus Quest 2',
                'descripcion' => 'Gafas de realidad virtual con juegos incluidos',
                'creditos' => 320,
                'usuario' => 'usuario_miguel_entrepreneur',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519023/huesjd27kvomjhmi3lnp.png'
            ],
            [
                'titulo' => 'GoPro Hero 9',
                'descripcion' => 'Cámara de acción con accesorios',
                'creditos' => 280,
                'usuario' => 'usuario_patricia_ux',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746518852/j6ikhhp4sfsdpa080kwe.png'
            ],
            [
                'titulo' => 'Nintendo Switch OLED',
                'descripcion' => 'Consola portátil con juegos populares',
                'creditos' => 350,
                'usuario' => 'usuario_sofia_tech',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519081/znybpgsmkb7wmdibtrrq.png'
            ],
            [
                'titulo' => 'Kindle Paperwhite',
                'descripcion' => 'E-reader con iluminación integrada',
                'creditos' => 120,
                'usuario' => 'usuario_pablo_design',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519153/kdhdrzntstgg2karnnx6.png'
            ]
        ];

        foreach ($objetos as $objetoData) {
            $objeto = new Objeto();
            $objeto->setTitulo($objetoData['titulo']);
            $objeto->setDescripcion($objetoData['descripcion']);
            $objeto->setCreditos($objetoData['creditos']);
            $objeto->setUsuario($this->getReference($objetoData['usuario'], Usuario::class));
            $objeto->setFechaCreacion(new \DateTimeImmutable());
            $objeto->setEstado($objetoData['estado']);
            $objeto->setImagen($objetoData['imagen']);
            $manager->persist($objeto);
            $this->addReference('objeto_' . strtolower(str_replace(' ', '_', $objetoData['titulo'])), $objeto);
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