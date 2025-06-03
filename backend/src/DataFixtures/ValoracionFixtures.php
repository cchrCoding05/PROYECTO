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

        // Valoraciones para Maria Garcia (diseñadora)
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

        // Valoraciones para Carlos Lopez (marketing)
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

        // Valoraciones para Ana Perez
        $valoracion5 = new Valoracion();
        $valoracion5->setUsuario($maria);
        $valoracion5->setProfesional($ana);
        $valoracion5->setPuntuacion(5);
        $valoracion5->setComentario('Muy buen servicio');
        $valoracion5->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion5);

        // Valoraciones para Sofia Tech
        $sofia = $this->getReference('usuario_sofia_tech', Usuario::class);
        
        $valoracion6 = new Valoracion();
        $valoracion6->setUsuario($admin);
        $valoracion6->setProfesional($sofia);
        $valoracion6->setPuntuacion(5);
        $valoracion6->setComentario('Excelente desarrolladora, muy profesional');
        $valoracion6->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion6);

        $valoracion7 = new Valoracion();
        $valoracion7->setUsuario($carlos);
        $valoracion7->setProfesional($sofia);
        $valoracion7->setPuntuacion(5);
        $valoracion7->setComentario('Gran trabajo en el desarrollo de la app');
        $valoracion7->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion7);

        // Valoraciones para Pablo Design
        $pablo = $this->getReference('usuario_pablo_design', Usuario::class);
        
        $valoracion8 = new Valoracion();
        $valoracion8->setUsuario($sofia);
        $valoracion8->setProfesional($pablo);
        $valoracion8->setPuntuacion(5);
        $valoracion8->setComentario('Diseño increíble, muy creativo');
        $valoracion8->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion8);

        // Valoraciones para Laura Marketing
        $laura = $this->getReference('usuario_laura_marketing', Usuario::class);
        
        $valoracion9 = new Valoracion();
        $valoracion9->setUsuario($pablo);
        $valoracion9->setProfesional($laura);
        $valoracion9->setPuntuacion(4);
        $valoracion9->setComentario('Buena estrategia de marketing');
        $valoracion9->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion9);

        // Valoraciones para Diego Dev
        $diego = $this->getReference('usuario_diego_dev', Usuario::class);
        
        $valoracion10 = new Valoracion();
        $valoracion10->setUsuario($laura);
        $valoracion10->setProfesional($diego);
        $valoracion10->setPuntuacion(5);
        $valoracion10->setComentario('Excelente desarrollador backend');
        $valoracion10->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion10);

        // Valoraciones para Roberto Consultant
        $roberto = $this->getReference('usuario_roberto_consultant', Usuario::class);
        
        $valoracion12 = new Valoracion();
        $valoracion12->setUsuario($roberto);
        $valoracion12->setProfesional($roberto);
        $valoracion12->setPuntuacion(4);
        $valoracion12->setComentario('Buen asesoramiento tecnológico');
        $valoracion12->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion12);

        // Valoraciones para Elena Teacher
        $elena = $this->getReference('usuario_elena_teacher', Usuario::class);
        
        $valoracion13 = new Valoracion();
        $valoracion13->setUsuario($roberto);
        $valoracion13->setProfesional($elena);
        $valoracion13->setPuntuacion(5);
        $valoracion13->setComentario('Excelente profesora, muy didáctica');
        $valoracion13->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion13);

        // Valoraciones para Miguel Entrepreneur
        $miguel = $this->getReference('usuario_miguel_entrepreneur', Usuario::class);
        
        $valoracion14 = new Valoracion();
        $valoracion14->setUsuario($elena);
        $valoracion14->setProfesional($miguel);
        $valoracion14->setPuntuacion(5);
        $valoracion14->setComentario('Gran mentor para startups');
        $valoracion14->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion14);

        // Valoraciones para Patricia UX
        $patricia = $this->getReference('usuario_patricia_ux', Usuario::class);
        
        $valoracion15 = new Valoracion();
        $valoracion15->setUsuario($miguel);
        $valoracion15->setProfesional($patricia);
        $valoracion15->setPuntuacion(5);
        $valoracion15->setComentario('Excelente investigación de usuarios');
        $valoracion15->setFechaCreacion(new \DateTimeImmutable());
        $manager->persist($valoracion15);

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