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
        $usuarios = [
            'juanperez', 'mariagarcia', 'carloslopez', 'anaperez', 'luismi', 'sofia_tech', 'pablo_design', 'laura_marketing', 'diego_dev', 'carmen_art', 'roberto_consultant', 'elena_teacher', 'miguel_entrepreneur', 'patricia_ux',
            'lucas_martinez', 'valeria_rios', 'david_gomez', 'laura_sanchez', 'andres_fernandez', 'carla_morales', 'sergio_ruiz', 'paula_ortiz', 'alejandro_vazquez', 'marta_rodriguez', 'javier_iglesias', 'sofia_mendez', 'rodrigo_silva', 'natalia_castro', 'francisco_molina', 'ines_garcia', 'adrian_lopez', 'patricia_santos', 'gonzalo_ramos', 'eva_martin', 'daniel_torres', 'monica_villa', 'victor_soto', 'alba_perez', 'ruben_cano', 'irene_sanz', 'oscar_moreno', 'noelia_fernandez'
        ];
        $objetos_varios = [
            [
                'titulo' => 'Bicicleta de montaña',
                'descripcion' => 'Bicicleta resistente para rutas y senderos.',
                'creditos' => 200,
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'
            ],
            [
                'titulo' => 'Subaru BRZ',
                'descripcion' => 'Coche deportivo japonés, ideal para drifting.',
                'creditos' => 18000,
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://images.unsplash.com/photo-1511918984145-48de785d4c4e?auto=format&fit=crop&w=400&q=80'
            ],
            [
                'titulo' => 'PlayStation 5',
                'descripcion' => 'Consola de última generación con mando.',
                'creditos' => 500,
                'estado' => Objeto::ESTADO_DISPONIBLE,
                'imagen' => 'https://images.unsplash.com/photo-1606813909358-0b7f1e3b6b8e?auto=format&fit=crop&w=400&q=80'
            ],
            ['titulo' => 'Mesilla de noche', 'descripcion' => 'Mueble pequeño de madera, dos cajones.', 'creditos' => 60, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'iPhone 13', 'descripcion' => 'Smartphone Apple, 128GB, color azul.', 'creditos' => 700, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara Nikon D3500', 'descripcion' => 'Cámara réflex digital para principiantes.', 'creditos' => 350, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Patinete eléctrico', 'descripcion' => 'Patinete urbano, batería de larga duración.', 'creditos' => 250, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla gamer', 'descripcion' => 'Silla ergonómica para largas sesiones.', 'creditos' => 120, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Monitor 27" 4K', 'descripcion' => 'Monitor UHD para diseño y gaming.', 'creditos' => 400, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Guitarra eléctrica', 'descripcion' => 'Guitarra Fender Stratocaster.', 'creditos' => 300, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Tablet Samsung Galaxy Tab', 'descripcion' => 'Tablet Android, pantalla 10.5".', 'creditos' => 220, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cafetera Nespresso', 'descripcion' => 'Cafetera automática, cápsulas incluidas.', 'creditos' => 80, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Aspiradora Dyson', 'descripcion' => 'Aspiradora sin cable, gran potencia.', 'creditos' => 180, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Apple Watch Series 7', 'descripcion' => 'Reloj inteligente, GPS, 44mm.', 'creditos' => 250, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta plegable', 'descripcion' => 'Ideal para ciudad y transporte público.', 'creditos' => 180, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Xbox Series X', 'descripcion' => 'Consola Microsoft, 1TB, mando incluido.', 'creditos' => 480, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Teclado mecánico', 'descripcion' => 'Teclado RGB switches azules.', 'creditos' => 90, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara GoPro Hero 10', 'descripcion' => 'Cámara de acción sumergible.', 'creditos' => 320, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Sofá 3 plazas', 'descripcion' => 'Sofá cómodo, color gris oscuro.', 'creditos' => 600, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Altavoz Bluetooth JBL', 'descripcion' => 'Altavoz portátil, resistente al agua.', 'creditos' => 70, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Nintendo Switch', 'descripcion' => 'Consola híbrida, dos mandos.', 'creditos' => 350, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta de paseo', 'descripcion' => 'Bicicleta clásica, color crema.', 'creditos' => 150, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Impresora HP LaserJet', 'descripcion' => 'Impresora láser monocromo.', 'creditos' => 110, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara Polaroid', 'descripcion' => 'Cámara instantánea, fotos retro.', 'creditos' => 90, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla de oficina', 'descripcion' => 'Silla giratoria, respaldo alto.', 'creditos' => 100, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'MacBook Air M2', 'descripcion' => 'Portátil Apple, ultraligero.', 'creditos' => 950, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Smart TV LG 50"', 'descripcion' => 'Televisor 4K, webOS.', 'creditos' => 700, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta fixie', 'descripcion' => 'Bicicleta urbana de piñón fijo.', 'creditos' => 170, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara Sony Alpha', 'descripcion' => 'Cámara mirrorless, 24MP.', 'creditos' => 600, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Reloj Casio Vintage', 'descripcion' => 'Reloj digital clásico.', 'creditos' => 40, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'iPad Mini', 'descripcion' => 'Tablet Apple, 64GB.', 'creditos' => 300, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta eléctrica', 'descripcion' => 'Bici urbana con motor eléctrico.', 'creditos' => 800, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Samsung Galaxy S22', 'descripcion' => 'Smartphone Android, 256GB.', 'creditos' => 650, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara deportiva', 'descripcion' => 'Cámara HD para deportes extremos.', 'creditos' => 110, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla plegable', 'descripcion' => 'Silla ligera para camping.', 'creditos' => 30, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta infantil', 'descripcion' => 'Bici para niños, ruedas de apoyo.', 'creditos' => 90, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Nintendo 3DS', 'descripcion' => 'Consola portátil, color azul.', 'creditos' => 120, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara Canon Powershot', 'descripcion' => 'Cámara compacta, fácil de usar.', 'creditos' => 80, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla de comedor', 'descripcion' => 'Silla de madera, estilo nórdico.', 'creditos' => 60, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta BMX', 'descripcion' => 'Bici para acrobacias y saltos.', 'creditos' => 140, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'PS4 Slim', 'descripcion' => 'Consola PlayStation 4, 500GB.', 'creditos' => 250, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara instantánea Fujifilm', 'descripcion' => 'Fotos al instante, color rosa.', 'creditos' => 70, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla ergonómica', 'descripcion' => 'Silla para oficina, soporte lumbar.', 'creditos' => 130, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta vintage', 'descripcion' => 'Bici antigua restaurada.', 'creditos' => 200, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Xbox One S', 'descripcion' => 'Consola Microsoft, 1TB.', 'creditos' => 200, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara deportiva 4K', 'descripcion' => 'Cámara para grabar aventuras.', 'creditos' => 150, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla de ruedas', 'descripcion' => 'Silla ligera y plegable.', 'creditos' => 300, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta de carretera', 'descripcion' => 'Bici para ciclismo de ruta.', 'creditos' => 350, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Nintendo Wii U', 'descripcion' => 'Consola de sobremesa Nintendo.', 'creditos' => 180, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara réflex Canon', 'descripcion' => 'Cámara profesional, 18MP.', 'creditos' => 400, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla gaming', 'descripcion' => 'Silla con luces LED.', 'creditos' => 160, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta urbana', 'descripcion' => 'Bici cómoda para ciudad.', 'creditos' => 130, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'PS Vita', 'descripcion' => 'Consola portátil Sony.', 'creditos' => 110, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara compacta Sony', 'descripcion' => 'Pequeña y fácil de llevar.', 'creditos' => 90, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla de jardín', 'descripcion' => 'Silla para exterior, color blanco.', 'creditos' => 40, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Bicicleta gravel', 'descripcion' => 'Bici para caminos mixtos.', 'creditos' => 300, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Xbox Series S', 'descripcion' => 'Consola Microsoft, digital.', 'creditos' => 300, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Cámara de acción', 'descripcion' => 'Cámara resistente al agua.', 'creditos' => 120, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
            ['titulo' => 'Silla alta para bar', 'descripcion' => 'Silla de madera alta.', 'creditos' => 70, 'estado' => Objeto::ESTADO_DISPONIBLE, 'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'],
        ];
        $objetos = [];
        $total_objetos = 60;
        $usuarios_count = count($usuarios);
        for ($i = 0; $i < $total_objetos; $i++) {
            $obj = $objetos_varios[$i % count($objetos_varios)];
            $usuario = $usuarios[$i % $usuarios_count];
            $objetos[] = [
                'titulo' => $obj['titulo'],
                'descripcion' => $obj['descripcion'],
                'creditos' => $obj['creditos'],
                'usuario' => 'usuario_' . $usuario,
                'estado' => $obj['estado'],
                'imagen' => $obj['imagen']
            ];
        }

        foreach ($objetos as $i => $objetoData) {
            $objeto = new Objeto();
            $objeto->setTitulo($objetoData['titulo']);
            $objeto->setDescripcion($objetoData['descripcion']);
            $objeto->setCreditos($objetoData['creditos']);
            $objeto->setUsuario($this->getReference($objetoData['usuario'], Usuario::class));
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