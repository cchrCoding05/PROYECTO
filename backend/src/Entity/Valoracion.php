<?php

namespace App\Entity;

use App\Repository\ValoracionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValoracionRepository::class)]
class Valoracion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_valoracion = null;

    #[ORM\ManyToOne(inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_intercambio_servicio', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioServicio $intercambio_servicio = null;

    #[ORM\ManyToOne(inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_intercambio_objeto', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioObjeto $intercambio_objeto = null;

    #[ORM\Column]
    private ?int $puntuacion = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comentario = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
    }

    public function getId_valoracion(): ?int
    {
        return $this->id_valoracion;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getIntercambioServicio(): ?IntercambioServicio
    {
        return $this->intercambio_servicio;
    }

    public function setIntercambioServicio(?IntercambioServicio $intercambio_servicio): self
    {
        $this->intercambio_servicio = $intercambio_servicio;
        return $this;
    }

    public function getIntercambioObjeto(): ?IntercambioObjeto
    {
        return $this->intercambio_objeto;
    }

    public function setIntercambioObjeto(?IntercambioObjeto $intercambio_objeto): self
    {
        $this->intercambio_objeto = $intercambio_objeto;
        return $this;
    }

    public function getPuntuacion(): ?int
    {
        return $this->puntuacion;
    }

    public function setPuntuacion(int $puntuacion): self
    {
        $this->puntuacion = $puntuacion;

        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }

    public function setComentario(?string $comentario): self
    {
        $this->comentario = $comentario;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fecha_creacion): self
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }
}