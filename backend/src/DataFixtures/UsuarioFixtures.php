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
            [ 'nombre_usuario' => 'ADMIN', 'correo' => 'admin@example.com', 'contrasena' => 'Admin123', 'profesion' => 'Administrador', 'descripcion' => 'Administrador del sistema', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747741177/k2frrjzbcgpyibv47l8m.png', 'creditos' => 999999 ],
            [ 'nombre_usuario' => 'juanperez', 'correo' => 'juan@example.com', 'contrasena' => 'Password123', 'profesion' => 'Desarrollador Web', 'descripcion' => 'Apasionado por la programación y el desarrollo web', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/10.jpg', 'creditos' => 12300 ],
            [ 'nombre_usuario' => 'mariagarcia', 'correo' => 'maria@example.com', 'contrasena' => 'Password123', 'profesion' => 'Diseñadora Gráfica', 'descripcion' => 'Especialista en diseño UI/UX', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/11.jpg', 'creditos' => 112323 ],
            [ 'nombre_usuario' => 'carloslopez', 'correo' => 'carlos@example.com', 'contrasena' => 'Password123', 'profesion' => 'Marketing Digital', 'descripcion' => 'Experto en estrategias de marketing online', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/12.jpg', 'creditos' => 4567 ],
            [ 'nombre_usuario' => 'anaperez', 'correo' => 'ana@example.com', 'contrasena' => 'Password123', 'profesion' => 'Estudiante', 'descripcion' => 'Estudiante de informática buscando objetos para aprender', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/13.jpg', 'creditos' => 1263478 ],
            [ 'nombre_usuario' => 'sofia_tech', 'correo' => 'sofia@example.com', 'contrasena' => 'Password123', 'profesion' => 'Ingeniera de Software', 'descripcion' => 'Especialista en desarrollo backend y arquitectura de software', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/15.jpg', 'creditos' => 25000 ],
            [ 'nombre_usuario' => 'pablo_design', 'correo' => 'pablo@example.com', 'contrasena' => 'Password123', 'profesion' => 'Diseñador UI/UX', 'descripcion' => 'Creador de experiencias digitales únicas', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/16.jpg', 'creditos' => 18000 ],
            [ 'nombre_usuario' => 'laura_marketing', 'correo' => 'laura@example.com', 'contrasena' => 'Password123', 'profesion' => 'Especialista en Marketing Digital', 'descripcion' => 'Estratega de marketing y crecimiento digital', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/17.jpg', 'creditos' => 15000 ],
            [ 'nombre_usuario' => 'diego_dev', 'correo' => 'diego@example.com', 'contrasena' => 'Password123', 'profesion' => 'Desarrollador Full Stack', 'descripcion' => 'Experto en desarrollo web y móvil', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/18.jpg', 'creditos' => 30000 ],
            [ 'nombre_usuario' => 'roberto_consultant', 'correo' => 'roberto@example.com', 'contrasena' => 'Password123', 'profesion' => 'Consultor Tecnológico', 'descripcion' => 'Asesor en transformación digital y estrategias tecnológicas', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/20.jpg', 'creditos' => 45000 ],
            [ 'nombre_usuario' => 'elena_teacher', 'correo' => 'elena@example.com', 'contrasena' => 'Password123', 'profesion' => 'Profesora de Programación', 'descripcion' => 'Educadora especializada en enseñanza de programación', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/21.jpg', 'creditos' => 28000 ],
            [ 'nombre_usuario' => 'miguel_entrepreneur', 'correo' => 'miguel@example.com', 'contrasena' => 'Password123', 'profesion' => 'Emprendedor Digital', 'descripcion' => 'Fundador de startups tecnológicas', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/22.jpg', 'creditos' => 50000 ],
            [ 'nombre_usuario' => 'patricia_ux', 'correo' => 'patricia@example.com', 'contrasena' => 'Password123', 'profesion' => 'Investigadora UX', 'descripcion' => 'Especialista en investigación de usuarios y experiencia de usuario', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/23.jpg', 'creditos' => 32000 ],
            [ 'nombre_usuario' => 'lucas_martinez', 'correo' => 'lucas.martinez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Ingeniero Civil', 'descripcion' => 'Apasionado por la construcción y el diseño.', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/24.jpg', 'creditos' => 1500 ],
            [ 'nombre_usuario' => 'valeria_rios', 'correo' => 'valeria.rios@example.com', 'contrasena' => 'Password123', 'profesion' => 'Psicóloga', 'descripcion' => 'Me encanta ayudar a las personas a crecer.', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/25.jpg', 'creditos' => 3200 ],
            [ 'nombre_usuario' => 'david_gomez', 'correo' => 'david.gomez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Músico', 'descripcion' => 'Toco la guitarra y compongo canciones.', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/26.jpg', 'creditos' => 2100 ],
            [ 'nombre_usuario' => 'laura_sanchez', 'correo' => 'laura.sanchez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Enfermera', 'descripcion' => 'Dedicada a cuidar y sanar.', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/27.jpg', 'creditos' => 1800 ],
            [ 'nombre_usuario' => 'andres_fernandez', 'correo' => 'andres.fernandez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Abogado', 'descripcion' => 'Defensor de la justicia y los derechos.', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/28.jpg', 'creditos' => 2700 ],
            [ 'nombre_usuario' => 'carla_morales', 'correo' => 'carla.morales@example.com', 'contrasena' => 'Password123', 'profesion' => 'Arquitecta', 'descripcion' => 'Diseño espacios funcionales y bellos.', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/29.jpg', 'creditos' => 3500 ],
            [ 'nombre_usuario' => 'sergio_ruiz', 'correo' => 'sergio.ruiz@example.com', 'contrasena' => 'Password123', 'profesion' => 'Veterinario', 'descripcion' => 'Amo a los animales y su bienestar.', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/30.jpg', 'creditos' => 1200 ],
            [ 'nombre_usuario' => 'paula_ortiz', 'correo' => 'paula.ortiz@example.com', 'contrasena' => 'Password123', 'profesion' => 'Periodista', 'descripcion' => 'Contando historias que importan.', 'foto_perfil' => 'https://randomuser.me/api/portraits/women/31.jpg', 'creditos' => 2600 ],
            [ 'nombre_usuario' => 'noelia_fernandez', 'correo' => 'noelia.fernandez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Química', 'descripcion' => 'Experimentando con la materia.', 'foto_perfil' => 'https://randomuser.me/api/portraits/men/14.jpg', 'creditos' => 2500 ],
        ];

        foreach ($usuarios as $usuarioData) {
            $usuario = new Usuario();
            $usuario->setNombreUsuario($usuarioData['nombre_usuario']);
            $usuario->setCorreo($usuarioData['correo']);
            $usuario->setContrasena($this->passwordHasher->hashPassword($usuario, $usuarioData['contrasena']));
            $usuario->setProfesion($usuarioData['profesion']);
            $usuario->setDescripcion($usuarioData['descripcion']);
            $usuario->setFechaRegistro(new \DateTimeImmutable());
            $usuario->setCreditos($usuarioData['creditos']);
            $usuario->setFotoPerfil($usuarioData['foto_perfil']);
            
            $manager->persist($usuario);
            $this->addReference('usuario_' . $usuarioData['nombre_usuario'], $usuario);
        }

        $manager->flush();
    }
}