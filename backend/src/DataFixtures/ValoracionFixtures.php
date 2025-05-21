<?php
namespace App\DataFixtures;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Entity\IntercambioServicio;
use App\Entity\IntercambioObjeto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ValoracionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Obtener usuarios para las pruebas
        $usuarios = $manager->getRepository(Usuario::class)->findAll();
        if (count($usuarios) < 5) {
            throw new \Exception('Se necesitan al menos 5 usuarios para las pruebas');
        }

        // Caso 1: Maria Garcia (valoraciones perfectas)
        $maria = $this->getReference('usuario_mariagarcia', Usuario::class);
        $juan = $this->getReference('usuario_juanperez', Usuario::class);
        $carlos = $this->getReference('usuario_carloslopez', Usuario::class);

        $valoracion1 = new Valoracion();
        $valoracion1->setUsuario($juan);
        $valoracion1->setProfesional($maria);
        $valoracion1->setPuntuacion(5);
        $valoracion1->setComentario('Excelente servicio, muy profesional');
        $valoracion1->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion1);

        $valoracion2 = new Valoracion();
        $valoracion2->setUsuario($carlos);
        $valoracion2->setProfesional($maria);
        $valoracion2->setPuntuacion(5);
        $valoracion2->setComentario('Increíble trabajo, lo recomiendo');
        $valoracion2->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion2);

        // Caso 2: Carlos Lopez (valoraciones mixtas)
        $ana = $this->getReference('usuario_anaperez', Usuario::class);
        $admin = $this->getReference('usuario_ADMIN', Usuario::class);

        $valoracion3 = new Valoracion();
        $valoracion3->setUsuario($admin);
        $valoracion3->setProfesional($carlos);
        $valoracion3->setPuntuacion(4);
        $valoracion3->setComentario('Buen servicio, pero podría mejorar');
        $valoracion3->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion3);

        $valoracion4 = new Valoracion();
        $valoracion4->setUsuario($ana);
        $valoracion4->setProfesional($carlos);
        $valoracion4->setPuntuacion(3);
        $valoracion4->setComentario('Servicio regular');
        $valoracion4->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion4);

        // Caso 3: Ana Perez (una sola valoración)
        $valoracion5 = new Valoracion();
        $valoracion5->setUsuario($maria);
        $valoracion5->setProfesional($ana);
        $valoracion5->setPuntuacion(5);
        $valoracion5->setComentario('Muy buen servicio');
        $valoracion5->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion5);

        $manager->flush();

        // Actualizar las valoraciones promedio de todos los usuarios
        foreach ($usuarios as $usuario) {
            $usuario->actualizarValoracionPromedio();
        }
        $manager->flush();

        // Crear referencias después de tener los IDs
        $valoracionesGuardadas = $manager->getRepository(Valoracion::class)->findAll();
        foreach ($valoracionesGuardadas as $valoracion) {
            $this->addReference('valoracion-' . $valoracion->getId_valoracion(), $valoracion);
        }
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            IntercambioServicioFixtures::class,
            IntercambioObjetoFixtures::class,
        ];
    }
}