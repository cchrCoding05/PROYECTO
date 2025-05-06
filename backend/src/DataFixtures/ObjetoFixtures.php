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
            // Objetos disponibles
            [
                'titulo' => 'Laptop HP EliteBook',
                'descripcion' => 'Laptop en excelente estado, ideal para trabajo y estudio',
                'creditos' => 200,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519023/huesjd27kvomjhmi3lnp.png'
            ],
            [
                'titulo' => 'Teclado Mecánico',
                'descripcion' => 'Teclado mecánico RGB con switches Cherry MX Red',
                'creditos' => 250,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746518852/j6ikhhp4sfsdpa080kwe.png'
            ],
            [
                'titulo' => 'Auriculares Sony',
                'descripcion' => 'Auriculares inalámbricos con cancelación de ruido',
                'creditos' => 180,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519081/znybpgsmkb7wmdibtrrq.png'  
            ],
            // Objetos reservados
            [
                'titulo' => 'Camara Canon EOS',
                'descripcion' => 'Camara profesional con lente 18-55mm, perfecta para fotografía',
                'creditos' => 300,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_RESERVADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519153/kdhdrzntstgg2karnnx6.png'
            ],
            [
                'titulo' => 'Monitor LG 27',
                'descripcion' => 'Monitor 4K con excelente calidad de imagen',
                'creditos' => 150,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_RESERVADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519178/i5xuqnltkmscwv3ihwae.png'
            ],
            // Objetos intercambiados
            [
                'titulo' => 'Smartphone Samsung',
                'descripcion' => 'Smartphone en perfecto estado, con todos sus accesorios',
                'creditos' => 350,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_INTERCAMBIADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519198/dsvjc3hzovw5egy6w4nr.png'
            ],
            [
                'titulo' => 'Tablet iPad',
                'descripcion' => 'Tablet Apple con lápiz digital incluido',
                'creditos' => 400,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_INTERCAMBIADO,
                'imagen' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519235/igkyggrvdu4ntg8jtrgq.png'
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