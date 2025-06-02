<?php

namespace App\Entity;

use App\Repository\MensajeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MensajeRepository::class)]
class Mensaje
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_mensaje = null;

    #[ORM\ManyToOne(inversedBy: 'mensajes_enviados')]
    #[ORM\JoinColumn(name: 'id_emisor', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $emisor = null;

    #[ORM\ManyToOne(inversedBy: 'mensajes_recibidos')]
    #[ORM\JoinColumn(name: 'id_receptor', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $receptor = null;

    #[ORM\Column(type: 'text')]
    private ?string $contenido = null;

    #[ORM\Column]
    private ?bool $leido = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_envio;

    #[ORM\ManyToOne(inversedBy: 'mensajes')]
    #[ORM\JoinColumn(name: 'id_negociacion_precio', referencedColumnName: 'id_negociacion', nullable: true)]
    private ?NegociacionPrecio $negociacion_precio = null;

    #[ORM\ManyToOne(inversedBy: 'mensajes')]
    #[ORM\JoinColumn(name: 'id_negociacion_servicio', referencedColumnName: 'id_negociacion', nullable: true)]
    private ?NegociacionServicio $negociacion_servicio = null;

    public function __construct()
    {
        $this->fecha_envio = new \DateTimeImmutable();
    }

    public function getId_mensaje(): ?int
    {
        return $this->id_mensaje;
    }

    public function getEmisor(): ?Usuario
    {
        return $this->emisor;
    }

    public function setEmisor(?Usuario $emisor): self
    {
        $this->emisor = $emisor;

        return $this;
    }

    public function getReceptor(): ?Usuario
    {
        return $this->receptor;
    }

    public function setReceptor(?Usuario $receptor): self
    {
        $this->receptor = $receptor;

        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): self
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function isLeido(): ?bool
    {
        return $this->leido;
    }

    public function setLeido(bool $leido): self
    {
        $this->leido = $leido;

        return $this;
    }

    public function getFechaEnvio(): ?\DateTimeImmutable
    {
        return $this->fecha_envio;
    }

    public function setFechaEnvio(\DateTimeImmutable $fecha_envio): self
    {
        $this->fecha_envio = $fecha_envio;

        return $this;
    }

    public function getNegociacionPrecio(): ?NegociacionPrecio
    {
        return $this->negociacion_precio;
    }

    public function setNegociacionPrecio(?NegociacionPrecio $negociacion_precio): self
    {
        $this->negociacion_precio = $negociacion_precio;
        return $this;
    }

    public function getNegociacionServicio(): ?NegociacionServicio
    {
        return $this->negociacion_servicio;
    }

    public function setNegociacionServicio(?NegociacionServicio $negociacion_servicio): self
    {
        $this->negociacion_servicio = $negociacion_servicio;
        return $this;
    }
}