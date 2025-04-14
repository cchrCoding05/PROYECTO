<?php

namespace App\Entity;

use App\Repository\EtiquetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtiquetaRepository::class)]
class Etiqueta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_etiqueta = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $nombre = null;

    #[ORM\ManyToMany(targetEntity: Servicio::class, mappedBy: 'etiquetas')]
    private Collection $servicios;

    public function __construct()
    {
        $this->servicios = new ArrayCollection();
    }

    public function getIdEtiqueta(): ?int
    {
        return $this->id_etiqueta;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection<int, Servicio>
     */
    public function getServicios(): Collection
    {
        return $this->servicios;
    }

    public function addServicio(Servicio $servicio): self
    {
        if (!$this->servicios->contains($servicio)) {
            $this->servicios->add($servicio);
            $servicio->addEtiqueta($this);
        }

        return $this;
    }

    public function removeServicio(Servicio $servicio): self
    {
        if ($this->servicios->removeElement($servicio)) {
            $servicio->removeEtiqueta($this);
        }

        return $this;
    }
}