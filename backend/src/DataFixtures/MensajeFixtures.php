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
                'emisor' => 'usuario-juanperez',
                'receptor' => 'usuario-mariagonzalez',
                'contenido' => '¡Hola María! Me gustaría hablar sobre tu trabajo de diseño',
                'leido' => true
            ],
            [
                'emisor' => 'usuario-mariagonzalez',
                'receptor' => 'usuario-juanperez',
                'contenido' => 'Hola Juan, claro que sí. ¿En qué puedo ayudarte?',
                'leido' => true
            ],
            [
                'emisor' => 'usuario-pedrosan',
                'receptor' => 'usuario-luciamartinez',
                'contenido' => 'Buenos días Lucia, ¿estarías disponible para una sesión de fotos este fin de semana?',
                'leido' => false
            ],
            [
                'emisor' => 'usuario-carlosrodriguez',
                'receptor' => 'usuario-analopez',
                'contenido' => 'Hola Ana, me encantaría aprender algunas recetas. ¿Podríamos intercambiar habilidades?',
                'leido' => false
            ],
            [
                'emisor' => 'usuario-analopez',
                'receptor' => 'usuario-juanperez',
                'contenido' => 'Juan, ¿podrías ayudarme con un problema en mi página web?',
                'leido' => true
            ],
            [
                'emisor' => 'usuario-luciamartinez',
                'receptor' => 'usuario-carlosrodriguez',
                'contenido' => 'Necesito un electricista para revisar la instalación de mi casa. ¿Estás disponible?',
                'leido' => false
            ]
        ];

        foreach ($mensajes as $mensajeData) {
            $mensaje = new Mensaje();
            $mensaje->setEmisor($this->getReference($mensajeData['emisor'], Usuario::class));
            $mensaje->setReceptor($this->getReference($mensajeData['receptor'], Usuario::class));
            $mensaje->setContenido($mensajeData['contenido']);
            $mensaje->setLeido($mensajeData['leido']);
            
            // La fecha de envío se establece automáticamente en el constructor
            
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