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

    #[ORM\ManyToOne(inversedBy: 'negociaciones')]
    #[ORM\JoinColumn(name: 'id_intercambio', referencedColumnName: 'id_intercambio', nullable: false)]
    private ?IntercambioObjeto $intercambio = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column]
    private ?int $creditos_propuestos = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mensaje = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_negociacion;

    public function __construct()
    {
        $this->fecha_negociacion = new \DateTimeImmutable();
    }

    public function getIdNegociacion(): ?int
    {
        return $this->id_negociacion;
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

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCreditosPropuestos(): ?int
    {
        return $this->creditos_propuestos;
    }

    public function setCreditosPropuestos(int $creditos_propuestos): self
    {
        $this->creditos_propuestos = $creditos_propuestos;

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

    public function getFechaNegociacion(): ?\DateTimeImmutable
    {
        return $this->fecha_negociacion;
    }

    public function setFechaNegociacion(\DateTimeImmutable $fecha_negociacion): self
    {
        $this->fecha_negociacion = $fecha_negociacion;

        return $this;
    }
}