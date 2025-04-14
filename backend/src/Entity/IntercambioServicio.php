<?php

namespace App\Entity;

use App\Repository\IntercambioServicioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntercambioServicioRepository::class)]
class IntercambioServicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_intercambio = null;

    #[ORM\ManyToOne(inversedBy: 'intercambios')]
    #[ORM\JoinColumn(name: 'id_servicio', referencedColumnName: 'id_servicio', nullable: false)]
    private ?Servicio $servicio = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_solicitante', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $solicitante = null;

    #[ORM\Column]
    private ?int $cantidad_creditos = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_solicitud;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fecha_completado = null;

    #[ORM\OneToMany(mappedBy: 'intercambio_servicio', targetEntity: Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\OneToMany(mappedBy: 'intercambio_servicio', targetEntity: TransaccionCredito::class)]
    private Collection $transacciones;

    public function __construct()
    {
        $this->fecha_solicitud = new \DateTimeImmutable();
        $this->valoraciones = new ArrayCollection();
        $this->transacciones = new ArrayCollection();
    }

    public function getIdIntercambio(): ?int
    {
        return $this->id_intercambio;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getSolicitante(): ?Usuario
    {
        return $this->solicitante;
    }

    public function setSolicitante(?Usuario $solicitante): self
    {
        $this->solicitante = $solicitante;

        return $this;
    }

    public function getCantidadCreditos(): ?int
    {
        return $this->cantidad_creditos;
    }

    public function setCantidadCreditos(int $cantidad_creditos): self
    {
        $this->cantidad_creditos = $cantidad_creditos;

        return $this;
    }

    public function getFechaSolicitud(): ?\DateTimeImmutable
    {
        return $this->fecha_solicitud;
    }

    public function setFechaSolicitud(\DateTimeImmutable $fecha_solicitud): self
    {
        $this->fecha_solicitud = $fecha_solicitud;

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
     * @return Collection<int, Valoracion>
     */
    public function getValoraciones(): Collection
    {
        return $this->valoraciones;
    }

    public function addValoracion(Valoracion $valoracion): self
    {
        if (!$this->valoraciones->contains($valoracion)) {
            $this->valoraciones->add($valoracion);
            $valoracion->setIntercambioServicio($this);
        }

        return $this;
    }

    public function removeValoracion(Valoracion $valoracion): self
    {
        if ($this->valoraciones->removeElement($valoracion)) {
            // set the owning side to null (unless already changed)
            if ($valoracion->getIntercambioServicio() === $this) {
                $valoracion->setIntercambioServicio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransaccionCredito>
     */
    public function getTransacciones(): Collection
    {
        return $this->transacciones;
    }

    public function addTransaccion(TransaccionCredito $transaccion): self
    {
        if (!$this->transacciones->contains($transaccion)) {
            $this->transacciones->add($transaccion);
            $transaccion->setIntercambioServicio($this);
        }

        return $this;
    }

    public function removeTransaccion(TransaccionCredito $transaccion): self
    {
        if ($this->transacciones->removeElement($transaccion)) {
            // set the owning side to null (unless already changed)
            if ($transaccion->getIntercambioServicio() === $this) {
                $transaccion->setIntercambioServicio(null);
            }
        }

        return $this;
    }
}