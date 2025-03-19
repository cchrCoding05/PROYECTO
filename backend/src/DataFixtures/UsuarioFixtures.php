<?php
namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsuarioFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $usuarios = [
            ['nombre_usuario' => 'juanperez', 'correo' => 'juan@example.com', 'contrasena' => 'password123', 'profesion' => 'Programador', 'descripcion' => 'Desarrollador web con 5 años de experiencia'],
            ['nombre_usuario' => 'mariagonzalez', 'correo' => 'maria@example.com', 'contrasena' => 'password456', 'profesion' => 'Diseñadora', 'descripcion' => 'Diseñadora gráfica especializada en UI/UX'],
            ['nombre_usuario' => 'pedrosan', 'correo' => 'pedro@example.com', 'contrasena' => 'password789', 'profesion' => 'Fotógrafo', 'descripcion' => 'Fotógrafo profesional de bodas y eventos'],
            ['nombre_usuario' => 'luciamartinez', 'correo' => 'lucia@example.com', 'contrasena' => 'password101', 'profesion' => 'Profesora', 'descripcion' => 'Profesora de matemáticas con 10 años de experiencia'],
            ['nombre_usuario' => 'carlosrodriguez', 'correo' => 'carlos@example.com', 'contrasena' => 'password202', 'profesion' => 'Electricista', 'descripcion' => 'Electricista certificado con experiencia en instalaciones residenciales'],
            ['nombre_usuario' => 'analopez', 'correo' => 'ana@example.com', 'contrasena' => 'password303', 'profesion' => 'Cocinera', 'descripcion' => 'Chef especializada en cocina mediterránea']
        ];

        foreach ($usuarios as $index => $userData) {
            $usuario = new Usuario();
            $usuario->setNombreUsuario($userData['nombre_usuario']);
            $usuario->setCorreo($userData['correo']);
            $usuario->setContrasena(password_hash($userData['contrasena'], PASSWORD_DEFAULT));
            $usuario->setProfesion($userData['profesion']);
            $usuario->setDescripcion($userData['descripcion']);
            $usuario->setCreditos(100 + $index * 50); // Cada usuario tiene una cantidad diferente de créditos

            $manager->persist($usuario);
            
            // Referencias para usar en otras fixtures
            $this->addReference('usuario-' . $userData['nombre_usuario'], $usuario);
        }

        $manager->flush();
    }
}