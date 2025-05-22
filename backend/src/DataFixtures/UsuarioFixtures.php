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
                'nombre_usuario' => 'ADMIN',
                'correo' => 'admin@example.com',
                'contrasena' => 'Admin123',
                'profesion' => 'Administrador',
                'descripcion' => 'Administrador del sistema',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747741177/k2frrjzbcgpyibv47l8m.png',
                'creditos' => 999999
            ],
            [
                'nombre_usuario' => 'juanperez',
                'correo' => 'juan@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Desarrollador Web',
                'descripcion' => 'Apasionado por la programación y el desarrollo web',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519718/l4x5zw2vezhieigtw9il.png',
                'creditos' => 12300
            ],
            [
                'nombre_usuario' => 'mariagarcia',
                'correo' => 'maria@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Diseñadora Gráfica',
                'descripcion' => 'Especialista en diseño UI/UX',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519692/wxzhqwn9aejcnqlklaoh.png',
                'creditos' => 112323
            ],
            [
                'nombre_usuario' => 'carloslopez',
                'correo' => 'carlos@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Marketing Digital',
                'descripcion' => 'Experto en estrategias de marketing online',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519663/thm9qdlxgfuqblbtnorp.png',
                'creditos' => 4567
            ],
            [
                'nombre_usuario' => 'anaperez',
                'correo' => 'ana@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Estudiante',
                'descripcion' => 'Estudiante de informática buscando objetos para aprender',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746534302/nzu6vroi6fhlybr6zbhu.png',
                'creditos' => 1263478
            ],
            [
                'nombre_usuario' => 'luismi',
                'correo' => 'luis@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Vendedor de tomates',
                'descripcion' => 'Vendedor de tomates',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747915523/j3uw1v5gcoxw95tuxbzz.png',
                'creditos' => 69
            ],
            [
                'nombre_usuario' => 'sofia_tech',
                'correo' => 'sofia@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Ingeniera de Software',
                'descripcion' => 'Especialista en desarrollo backend y arquitectura de software',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519718/l4x5zw2vezhieigtw9il.png',
                'creditos' => 25000
            ],
            [
                'nombre_usuario' => 'pablo_design',
                'correo' => 'pablo@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Diseñador UI/UX',
                'descripcion' => 'Creador de experiencias digitales únicas',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519692/wxzhqwn9aejcnqlklaoh.png',
                'creditos' => 18000
            ],
            [
                'nombre_usuario' => 'laura_marketing',
                'correo' => 'laura@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Especialista en Marketing Digital',
                'descripcion' => 'Estratega de marketing y crecimiento digital',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519663/thm9qdlxgfuqblbtnorp.png',
                'creditos' => 15000
            ],
            [
                'nombre_usuario' => 'diego_dev',
                'correo' => 'diego@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Desarrollador Full Stack',
                'descripcion' => 'Experto en desarrollo web y móvil',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746534302/nzu6vroi6fhlybr6zbhu.png',
                'creditos' => 30000
            ],
            [
                'nombre_usuario' => 'carmen_art',
                'correo' => 'carmen@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Artista Digital',
                'descripcion' => 'Creadora de arte digital y diseño conceptual',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747915523/j3uw1v5gcoxw95tuxbzz.png',
                'creditos' => 22000
            ],
            [
                'nombre_usuario' => 'roberto_consultant',
                'correo' => 'roberto@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Consultor Tecnológico',
                'descripcion' => 'Asesor en transformación digital y estrategias tecnológicas',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519718/l4x5zw2vezhieigtw9il.png',
                'creditos' => 45000
            ],
            [
                'nombre_usuario' => 'elena_teacher',
                'correo' => 'elena@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Profesora de Programación',
                'descripcion' => 'Educadora especializada en enseñanza de programación',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519692/wxzhqwn9aejcnqlklaoh.png',
                'creditos' => 28000
            ],
            [
                'nombre_usuario' => 'miguel_entrepreneur',
                'correo' => 'miguel@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Emprendedor Digital',
                'descripcion' => 'Fundador de startups tecnológicas',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746519663/thm9qdlxgfuqblbtnorp.png',
                'creditos' => 50000
            ],
            [
                'nombre_usuario' => 'patricia_ux',
                'correo' => 'patricia@example.com',
                'contrasena' => 'Password123',
                'profesion' => 'Investigadora UX',
                'descripcion' => 'Especialista en investigación de usuarios y experiencia de usuario',
                'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1746534302/nzu6vroi6fhlybr6zbhu.png',
                'creditos' => 32000
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
            $usuario->setCreditos($usuarioData['creditos']);
            $usuario->setFotoPerfil($usuarioData['foto_perfil']);
            
            $manager->persist($usuario);
            $this->addReference('usuario_' . $usuarioData['nombre_usuario'], $usuario);
        }

        $manager->flush();
    }
}