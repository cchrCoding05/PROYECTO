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
            ],
            [ 'nombre_usuario' => 'lucas_martinez', 'correo' => 'lucas.martinez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Ingeniero Civil', 'descripcion' => 'Apasionado por la construcción y el diseño.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276418/tocsuxufoyzxdcixr7af.png', 'creditos' => 1500 ],
            [ 'nombre_usuario' => 'valeria_rios', 'correo' => 'valeria.rios@example.com', 'contrasena' => 'Password123', 'profesion' => 'Psicóloga', 'descripcion' => 'Me encanta ayudar a las personas a crecer.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276378/ucoyrcleonqbel0iswfs.png', 'creditos' => 3200 ],
            [ 'nombre_usuario' => 'david_gomez', 'correo' => 'david.gomez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Músico', 'descripcion' => 'Toco la guitarra y compongo canciones.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276549/m9zcdrzg7pqplo9hoto6.png', 'creditos' => 2100 ],
            [ 'nombre_usuario' => 'laura_sanchez', 'correo' => 'laura.sanchez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Enfermera', 'descripcion' => 'Dedicada a cuidar y sanar.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276330/phwjubesmezmsknviimx.png', 'creditos' => 1800 ],
            [ 'nombre_usuario' => 'andres_fernandez', 'correo' => 'andres.fernandez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Abogado', 'descripcion' => 'Defensor de la justicia y los derechos.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276522/bgm5hoiweml5stsq5v1m.png', 'creditos' => 2700 ],
            [ 'nombre_usuario' => 'carla_morales', 'correo' => 'carla.morales@example.com', 'contrasena' => 'Password123', 'profesion' => 'Arquitecta', 'descripcion' => 'Diseño espacios funcionales y bellos.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747234795/umfmrqqqf4pwaedtkviq.jpg', 'creditos' => 3500 ],
            [ 'nombre_usuario' => 'sergio_ruiz', 'correo' => 'sergio.ruiz@example.com', 'contrasena' => 'Password123', 'profesion' => 'Veterinario', 'descripcion' => 'Amo a los animales y su bienestar.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276418/tocsuxufoyzxdcixr7af.png', 'creditos' => 1200 ],
            [ 'nombre_usuario' => 'paula_ortiz', 'correo' => 'paula.ortiz@example.com', 'contrasena' => 'Password123', 'profesion' => 'Periodista', 'descripcion' => 'Contando historias que importan.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1747234795/umfmrqqqf4pwaedtkviq.jpg', 'creditos' => 2600 ],
            [ 'nombre_usuario' => 'noelia_fernandez', 'correo' => 'noelia.fernandez@example.com', 'contrasena' => 'Password123', 'profesion' => 'Química', 'descripcion' => 'Experimentando con la materia.', 'foto_perfil' => 'https://res.cloudinary.com/dhi3vddex/image/upload/v1748276330/phwjubesmezmsknviimx.png', 'creditos' => 2500 ],
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