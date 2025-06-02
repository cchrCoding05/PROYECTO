<?php

namespace App\Entity;

use App\Repository\NegociacionServicioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NegociacionServicioRepository::class)]
class NegociacionServicio
{
    // Estados de la negociación
    public const ESTADO_EN_NEGOCIACION = 'EN_NEGOCIACION';
    public const ESTADO_ACEPTADA = 'ACEPTADA';
    public const ESTADO_COMPLETADA = 'COMPLETADA';
    public const ESTADO_RECHAZADA = 'RECHAZADA';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_negociacion = null;

    #[ORM\ManyToOne(inversedBy: 'negociaciones_servicio_cliente')]
    #[ORM\JoinColumn(name: 'id_cliente', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $cliente = null;

    #[ORM\ManyToOne(inversedBy: 'negociaciones_servicio_profesional')]
    #[ORM\JoinColumn(name: 'id_profesional', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $profesional = null;

    #[ORM\Column]
    private ?int $creditos_propuestos = null;

    #[ORM\Column(length: 20)]
    private ?string $estado = self::ESTADO_EN_NEGOCIACION;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fecha_aceptacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fecha_completado = null;

    #[ORM\OneToMany(mappedBy: 'negociacion_servicio', targetEntity: Mensaje::class)]
    private Collection $mensajes;

    #[ORM\OneToOne(mappedBy: 'negociacion_servicio', targetEntity: Valoracion::class)]
    private ?Valoracion $valoracion = null;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
        $this->mensajes = new ArrayCollection();
    }

    public function getId_negociacion(): ?int
    {
        return $this->id_negociacion;
    }

    public function getCliente(): ?Usuario
    {
        return $this->cliente;
    }

    public function setCliente(?Usuario $cliente): self
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getProfesional(): ?Usuario
    {
        return $this->profesional;
    }

    public function setProfesional(?Usuario $profesional): self
    {
        $this->profesional = $profesional;
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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;
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

    public function getFechaAceptacion(): ?\DateTimeImmutable
    {
        return $this->fecha_aceptacion;
    }

    public function setFechaAceptacion(?\DateTimeImmutable $fecha_aceptacion): self
    {
        $this->fecha_aceptacion = $fecha_aceptacion;
        return $this;
    }

    public function getFechaCompletado(): ?\DateTimeImmutable
    {
        return $this->fecha_completado;
    }

    public function setFechaCompletado(?\DateTimeImmutable $fecha_completado): self
    {
        $this->fecha_completado = $fecha_completado;
        return $this;
    }

    /**
     * @return Collection<int, Mensaje>
     */
    public function getMensajes(): Collection
    {
        return $this->mensajes;
    }

    public function addMensaje(Mensaje $mensaje): self
    {
        if (!$this->mensajes->contains($mensaje)) {
            $this->mensajes->add($mensaje);
            $mensaje->setNegociacionServicio($this);
        }
        return $this;
    }

    public function removeMensaje(Mensaje $mensaje): self
    {
        if ($this->mensajes->removeElement($mensaje)) {
            if ($mensaje->getNegociacionServicio() === $this) {
                $mensaje->setNegociacionServicio(null);
            }
        }
        return $this;
    }

    public function getValoracion(): ?Valoracion
    {
        return $this->valoracion;
    }

    public function setValoracion(?Valoracion $valoracion): self
    {
        $this->valoracion = $valoracion;
        return $this;
    }

    public function aceptar(): self
    {
        $this->estado = self::ESTADO_ACEPTADA;
        $this->fecha_aceptacion = new \DateTimeImmutable();
        return $this;
    }

    public function completar(): self
    {
        $this->estado = self::ESTADO_COMPLETADA;
        $this->fecha_completado = new \DateTimeImmutable();
        return $this;
    }

    public function rechazar(): self
    {
        $this->estado = self::ESTADO_RECHAZADA;
        return $this;
    }
} 