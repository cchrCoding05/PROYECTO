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
                'estado' => Objeto::ESTADO_DISPONIBLE
            ],
            [
                'titulo' => 'Teclado Mecánico',
                'descripcion' => 'Teclado mecánico RGB con switches Cherry MX Red',
                'creditos' => 250,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_DISPONIBLE
            ],
            [
                'titulo' => 'Auriculares Sony',
                'descripcion' => 'Auriculares inalámbricos con cancelación de ruido',
                'creditos' => 180,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_DISPONIBLE
            ],
            // Objetos reservados
            [
                'titulo' => 'Camara Canon EOS',
                'descripcion' => 'Camara profesional con lente 18-55mm, perfecta para fotografía',
                'creditos' => 300,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_RESERVADO
            ],
            [
                'titulo' => 'Monitor LG 27',
                'descripcion' => 'Monitor 4K con excelente calidad de imagen',
                'creditos' => 150,
                'usuario' => 'usuario_carloslopez',
                'estado' => Objeto::ESTADO_RESERVADO
            ],
            // Objetos intercambiados
            [
                'titulo' => 'Smartphone Samsung',
                'descripcion' => 'Smartphone en perfecto estado, con todos sus accesorios',
                'creditos' => 350,
                'usuario' => 'usuario_juanperez',
                'estado' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'titulo' => 'Tablet iPad',
                'descripcion' => 'Tablet Apple con lápiz digital incluido',
                'creditos' => 400,
                'usuario' => 'usuario_mariagarcia',
                'estado' => Objeto::ESTADO_INTERCAMBIADO
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