<?php
namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usuarios = [
            [
                'nombre_usuario' => 'juanperez',
                'correo' => 'juan@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Desarrollador Web',
                'descripcion' => 'Apasionado por la programación y el desarrollo web'
            ],
            [
                'nombre_usuario' => 'mariagarcia',
                'correo' => 'maria@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Diseñadora Gráfica',
                'descripcion' => 'Especialista en diseño UI/UX'
            ],
            [
                'nombre_usuario' => 'carloslopez',
                'correo' => 'carlos@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Marketing Digital',
                'descripcion' => 'Experto en estrategias de marketing online'
            ]
        ];

        foreach ($usuarios as $usuarioData) {
            $usuario = new Usuario();
            $usuario->setNombreUsuario($usuarioData['nombre_usuario']);
            $usuario->setCorreo($usuarioData['correo']);
            $usuario->setContrasena($this->passwordHasher->hashPassword($usuario, $usuarioData['contrasena']));
            $usuario->setProfesion($usuarioData['profesion']);
            $usuario->setDescripcion($usuarioData['descripcion']);
            $usuario->setFechaRegistro(new \DateTimeImmutable());
            $usuario->setCreditos(100);

            $manager->persist($usuario);
            $this->addReference('usuario_' . $usuarioData['nombre_usuario'], $usuario);
        }

        $manager->flush();
    }
}