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
        $objetos_varios = [
            [
                'titulo' => 'Subaru BRZ',
                'descripcion' => 'Coche deportivo japonés, ideal para drifting.',
                'creditos' => 18000,
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Mesilla de noche', 
                'descripcion' => 'Mueble pequeño de madera, dos cajones.', 
                'creditos' => 60, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'iPhone 13', 
                'descripcion' => 'Smartphone Apple, 128GB, color azul.', 
                'creditos' => 700, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Cámara Nikon D3500', 
                'descripcion' => 'Cámara réflex digital para principiantes.', 
                'creditos' => 350, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Patinete eléctrico', 
                'descripcion' => 'Patinete urbano, batería de larga duración.', 
                'creditos' => 250, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Silla gamer', 
                'descripcion' => 'Silla ergonómica para largas sesiones.', 
                'creditos' => 120, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1541558869434-2840d308329a?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Monitor 27" 4K', 
                'descripcion' => 'Monitor UHD para diseño y gaming.', 
                'creditos' => 400, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Guitarra eléctrica', 
                'descripcion' => 'Guitarra Fender Stratocaster.', 
                'creditos' => 300, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1525201548942-d8732f6617a0?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Tablet Samsung Galaxy Tab', 
                'descripcion' => 'Tablet Android, pantalla 10.5".', 
                'creditos' => 220, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1561154464-82e9adf32764?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Cafetera Nespresso', 
                'descripcion' => 'Cafetera automática, cápsulas incluidas.', 
                'creditos' => 80, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Aspiradora Dyson', 
                'descripcion' => 'Aspiradora sin cable, gran potencia.', 
                'creditos' => 180, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Apple Watch Series 7', 
                'descripcion' => 'Reloj inteligente, GPS, 44mm.', 
                'creditos' => 250, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1551816230-ef5deaed4a26?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Xbox Series X', 
                'descripcion' => 'Consola Microsoft, 1TB, mando incluido.', 
                'creditos' => 480, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Teclado mecánico', 
                'descripcion' => 'Teclado RGB switches azules.', 
                'creditos' => 90, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Sofá 3 plazas', 
                'descripcion' => 'Sofá cómodo, color gris oscuro.', 
                'creditos' => 600, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Altavoz Bluetooth JBL', 
                'descripcion' => 'Altavoz portátil, resistente al agua.', 
                'creditos' => 70, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Nintendo Switch', 
                'descripcion' => 'Consola híbrida, dos mandos.', 
                'creditos' => 350, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Cámara de acción', 
                'descripcion' => 'Cámara resistente al agua.', 
                'creditos' => 120, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'titulo' => 'Silla alta para bar', 
                'descripcion' => 'Silla de madera alta.', 
                'creditos' => 70, 
                'estado' => Objeto::ESTADO_DISPONIBLE, 
                'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80'
            ],
        ];
        
        // Lista de usuarios (excluyendo al admin)
        $usuarios = [
            'juanperez',
            'mariagarcia',
            'carloslopez',
            'anaperez',
            'sofia_tech',
            'pablo_design',
            'laura_marketing',
            'diego_dev',
            'roberto_consultant',
            'elena_teacher',
            'miguel_entrepreneur',
            'lucas_martinez',
            'valeria_rios',
            'david_gomez',
            'laura_sanchez',
            'andres_fernandez',
            'carla_morales',
            'sergio_ruiz',
            'paula_ortiz',
            'noelia_fernandez'
        ];
        
        $objetos = [];
        $total_objetos = 60;
        
        for ($i = 0; $i < $total_objetos; $i++) {
            $obj = $objetos_varios[$i % count($objetos_varios)];
            // Seleccionar un usuario aleatorio
            $usuarioRandom = $usuarios[array_rand($usuarios)];
            
            $objetos[] = [
                'titulo' => $obj['titulo'],
                'descripcion' => $obj['descripcion'],
                'creditos' => $obj['creditos'],
                'usuario' => $usuarioRandom,
                'estado' => $obj['estado'],
                'imagen' => $obj['imagen']
            ];
        }

        foreach ($objetos as $i => $objetoData) {
            $objeto = new Objeto();
            $objeto->setTitulo($objetoData['titulo']);
            $objeto->setDescripcion($objetoData['descripcion']);
            $objeto->setCreditos($objetoData['creditos']);
            $objeto->setUsuario($this->getReference('usuario_' . $objetoData['usuario'], Usuario::class));
            $objeto->setFechaCreacion(new \DateTimeImmutable());
            $objeto->setEstado($objetoData['estado']);
            $objeto->setImagen($objetoData['imagen']);
            $manager->persist($objeto);
            $this->addReference('objeto_' . strtolower(str_replace(' ', '_', $objetoData['titulo'])) . '_' . $i, $objeto);
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