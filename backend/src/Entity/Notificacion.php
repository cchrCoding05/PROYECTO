<?php

namespace App\Entity;

use App\Repository\NotificacionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificacionRepository::class)]
#[ORM\Table(name: 'notificaciones')]
class Notificacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_notificacion')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['mensaje', 'propuesta_producto', 'propuesta_profesional', 'propuesta_servicio'])]
    private ?string $tipo = null;

    #[ORM\Column(type: 'text')]
    private ?string $mensaje = null;

    #[ORM\Column(name: 'leido', type: 'boolean', options: ['default' => false])]
    private bool $leido = false;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\Column(name: 'referencia_id')]
    private ?int $referenciaId = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'emisor_id', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $emisor = null;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getMensaje(): ?string
    {
        return $this->mensaje;
    }

    public function setMensaje(string $mensaje): static
    {
        $this->mensaje = $mensaje;
        return $this;
    }

    public function isLeido(): bool
    {
        return $this->leido;
    }

    public function setLeido(bool $leido): static
    {
        $this->leido = $leido;
        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;
        return $this;
    }

    public function getReferenciaId(): ?int
    {
        return $this->referenciaId;
    }

    public function setReferenciaId(int $referenciaId): static
    {
        $this->referenciaId = $referenciaId;
        return $this;
    }

    public function getEmisor(): ?Usuario
    {
        return $this->emisor;
    }

    public function setEmisor(?Usuario $emisor): static
    {
        $this->emisor = $emisor;
        return $this;
    }
} 