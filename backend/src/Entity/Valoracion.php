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
    #[ORM\JoinColumn(name: 'id_intercambio_servicio', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioServicio $intercambio_servicio = null;

    #[ORM\ManyToOne(inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(name: 'id_intercambio_objeto', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioObjeto $intercambio_objeto = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_evaluador', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $evaluador = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_evaluado', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $evaluado = null;

    #[ORM\Column]
    private ?int $puntuacion = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comentario = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_valoracion;

    public function __construct()
    {
        $this->fecha_valoracion = new \DateTimeImmutable();
    }

    public function getId_valoracion(): ?int
    {
        return $this->id_valoracion;
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

    public function getEvaluador(): ?Usuario
    {
        return $this->evaluador;
    }

    public function setEvaluador(?Usuario $evaluador): self
    {
        $this->evaluador = $evaluador;

        return $this;
    }

    public function getEvaluado(): ?Usuario
    {
        return $this->evaluado;
    }

    public function setEvaluado(?Usuario $evaluado): self
    {
        $this->evaluado = $evaluado;

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

    public function getFechaValoracion(): ?\DateTimeImmutable
    {
        return $this->fecha_valoracion;
    }

    public function setFechaValoracion(\DateTimeImmutable $fecha_valoracion): self
    {
        $this->fecha_valoracion = $fecha_valoracion;

        return $this;
    }
}