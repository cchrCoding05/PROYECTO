<?php

namespace App\Entity;

use App\Repository\TransaccionCreditoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransaccionCreditoRepository::class)]
class TransaccionCredito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_transaccion = null;

    #[ORM\ManyToOne(inversedBy: 'transacciones_credito')]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(length: 20)]
    private ?string $tipo = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    #[ORM\ManyToOne(inversedBy: 'transacciones')]
    #[ORM\JoinColumn(name: 'id_intercambio_servicio', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioServicio $intercambio_servicio = null;

    #[ORM\ManyToOne(inversedBy: 'transacciones')]
    #[ORM\JoinColumn(name: 'id_intercambio_objeto', referencedColumnName: 'id_intercambio', nullable: true)]
    private ?IntercambioObjeto $intercambio_objeto = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
    }

    public function getId_transaccion(): ?int
    {
        return $this->id_transaccion;
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

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;
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