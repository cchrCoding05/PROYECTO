<?php
namespace App\DataFixtures;

use App\Entity\Mensaje;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MensajeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $mensajes = [
            [
                'contenido' => 'Hola, me interesa tu servicio de desarrollo web. ¿Podrías darme más detalles?',
                'emisor' => 'usuario_mariagarcia',
                'receptor' => 'usuario_juanperez',
                'leido' => true
            ],
            [
                'contenido' => 'Claro, con gusto te explico los detalles del servicio. ¿Qué tipo de sitio web necesitas?',
                'emisor' => 'usuario_juanperez',
                'receptor' => 'usuario_mariagarcia',
                'leido' => false
            ],
            [
                'contenido' => '¿Te interesaría colaborar en un proyecto de marketing digital?',
                'emisor' => 'usuario_carloslopez',
                'receptor' => 'usuario_mariagarcia',
                'leido' => true
            ]
        ];

        foreach ($mensajes as $mensajeData) {
            $mensaje = new Mensaje();
            $mensaje->setContenido($mensajeData['contenido']);
            $mensaje->setEmisor($this->getReference($mensajeData['emisor'], Usuario::class));
            $mensaje->setReceptor($this->getReference($mensajeData['receptor'], Usuario::class));
            $mensaje->setLeido($mensajeData['leido']);
            $mensaje->setFechaEnvio(new \DateTimeImmutable());

            $manager->persist($mensaje);
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