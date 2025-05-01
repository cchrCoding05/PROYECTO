<?php

namespace App\Entity;

use App\Repository\NegociacionPrecioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NegociacionPrecioRepository::class)]
class NegociacionPrecio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_negociacion = null;

    #[ORM\ManyToOne(inversedBy: 'negociaciones_precio')]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'negociaciones')]
    #[ORM\JoinColumn(name: 'id_intercambio', referencedColumnName: 'id_intercambio', nullable: false)]
    private ?IntercambioObjeto $intercambio = null;

    #[ORM\Column]
    private ?int $precio_propuesto = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mensaje = null;

    #[ORM\Column]
    private ?bool $aceptado = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
    }

    public function getId_negociacion(): ?int
    {
        return $this->id_negociacion;
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

    public function getIntercambio(): ?IntercambioObjeto
    {
        return $this->intercambio;
    }

    public function setIntercambio(?IntercambioObjeto $intercambio): self
    {
        $this->intercambio = $intercambio;

        return $this;
    }

    public function getPrecioPropuesto(): ?int
    {
        return $this->precio_propuesto;
    }

    public function setPrecioPropuesto(int $precio_propuesto): self
    {
        $this->precio_propuesto = $precio_propuesto;

        return $this;
    }

    public function getMensaje(): ?string
    {
        return $this->mensaje;
    }

    public function setMensaje(?string $mensaje): self
    {
        $this->mensaje = $mensaje;
        return $this;
    }

    public function isAceptado(): ?bool
    {
        return $this->aceptado;
    }

    public function setAceptado(bool $aceptado): self
    {
        $this->aceptado = $aceptado;

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