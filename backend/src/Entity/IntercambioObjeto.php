<?php

namespace App\Entity;

use App\Repository\IntercambioObjetoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntercambioObjetoRepository::class)]
class IntercambioObjeto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_intercambio = null;

    #[ORM\ManyToOne(inversedBy: 'intercambios')]
    #[ORM\JoinColumn(name: 'id_objeto', referencedColumnName: 'id_objeto', nullable: false)]
    private ?Objeto $objeto = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_vendedor', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $vendedor = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_comprador', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $comprador = null;

    #[ORM\Column]
    private ?int $creditos_propuestos = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_solicitud;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fecha_completado = null;

    #[ORM\OneToMany(mappedBy: 'intercambio', targetEntity: NegociacionPrecio::class)]
    private Collection $negociaciones;

    #[ORM\OneToMany(mappedBy: 'intercambio_objeto', targetEntity: Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\OneToMany(mappedBy: 'intercambio_objeto', targetEntity: TransaccionCredito::class)]
    private Collection $transacciones;

    public function __construct()
    {
        $this->fecha_solicitud = new \DateTimeImmutable();
        $this->negociaciones = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
        $this->transacciones = new ArrayCollection();
    }

    public function getIdIntercambio(): ?int
    {
        return $this->id_intercambio;
    }

    public function getObjeto(): ?Objeto
    {
        return $this->objeto;
    }

    public function setObjeto(?Objeto $objeto): self
    {
        $this->objeto = $objeto;

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

    public function getComprador(): ?Usuario
    {
        return $this->comprador;
    }

    public function setComprador(?Usuario $comprador): self
    {
        $this->comprador = $comprador;

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
     * @return Collection<int, NegociacionPrecio>
     */
    public function getNegociaciones(): Collection
    {
        return $this->negociaciones;
    }

    public function addNegociacion(NegociacionPrecio $negociacion): self
    {
        if (!$this->negociaciones->contains($negociacion)) {
            $this->negociaciones->add($negociacion);
            $negociacion->setIntercambio($this);
        }

        return $this;
    }

    public function removeNegociacion(NegociacionPrecio $negociacion): self
    {
        if ($this->negociaciones->removeElement($negociacion)) {
            // set the owning side to null (unless already changed)
            if ($negociacion->getIntercambio() === $this) {
                $negociacion->setIntercambio(null);
            }
        }

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
            $valoracion->setIntercambioObjeto($this);
        }

        return $this;
    }

    public function removeValoracion(Valoracion $valoracion): self
    {
        if ($this->valoraciones->removeElement($valoracion)) {
            // set the owning side to null (unless already changed)
            if ($valoracion->getIntercambioObjeto() === $this) {
                $valoracion->setIntercambioObjeto(null);
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
            $transaccion->setIntercambioObjeto($this);
        }

        return $this;
    }

    public function removeTransaccion(TransaccionCredito $transaccion): self
    {
        if ($this->transacciones->removeElement($transaccion)) {
            // set the owning side to null (unless already changed)
            if ($transaccion->getIntercambioObjeto() === $this) {
                $transaccion->setIntercambioObjeto(null);
            }
        }

        return $this;
    }
}