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
    #[ORM\JoinColumn(name: 'id_comprador', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $comprador = null;

    #[ORM\ManyToOne(inversedBy: 'negociaciones_precio')]
    #[ORM\JoinColumn(name: 'id_vendedor', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $vendedor = null;

    #[ORM\Column]
    private ?int $precio_propuesto = null;

    #[ORM\Column]
    private ?bool $aceptado = false;

    #[ORM\Column]
    private ?bool $aceptado_vendedor = false;

    #[ORM\Column]
    private ?bool $aceptado_comprador = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    #[ORM\ManyToOne(inversedBy: 'negociaciones')]
    #[ORM\JoinColumn(name: 'id_intercambio', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioObjeto $intercambio = null;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
    }

    public function getId_negociacion(): ?int
    {
        return $this->id_negociacion;
    }

    public function getComprador(): ?Usuario
    {
        return $this->comprador;
    }

    public function setComprador(?Usuario $comprador): self
    {
        $this->comprador = $comprador;
        return $this;
    }

    public function getVendedor(): ?Usuario
    {
        return $this->vendedor;
    }

    public function setVendedor(?Usuario $vendedor): self
    {
        $this->vendedor = $vendedor;
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

    public function isAceptado(): ?bool
    {
        return $this->aceptado;
    }

    public function setAceptado(bool $aceptado): self
    {
        $this->aceptado = $aceptado;
        return $this;
    }

    public function isAceptadoVendedor(): ?bool
    {
        return $this->aceptado_vendedor;
    }

    public function setAceptadoVendedor(bool $aceptado_vendedor): self
    {
        $this->aceptado_vendedor = $aceptado_vendedor;
        return $this;
    }

    public function isAceptadoComprador(): ?bool
    {
        return $this->aceptado_comprador;
    }

    public function setAceptadoComprador(bool $aceptado_comprador): self
    {
        $this->aceptado_comprador = $aceptado_comprador;
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

    public function getIntercambio(): ?IntercambioObjeto
    {
        return $this->intercambio;
    }

    public function setIntercambio(?IntercambioObjeto $intercambio): self
    {
        $this->intercambio = $intercambio;
        return $this;
    }
}