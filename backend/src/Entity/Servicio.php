<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicioRepository::class)]
class Servicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_servicio = null;

    #[ORM\ManyToOne(inversedBy: 'servicios')]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text')]
    private ?string $descripcion = null;

    #[ORM\ManyToOne(inversedBy: 'servicios')]
    #[ORM\JoinColumn(name: 'id_categoria', referencedColumnName: 'id_categoria', nullable: false)]
    private ?Categoria $categoria = null;

    #[ORM\Column]
    private ?int $creditos = null;

    #[ORM\Column]
    private ?bool $activo = true;

    #[ORM\OneToMany(mappedBy: 'servicio', targetEntity: ImagenServicio::class, cascade: ['persist', 'remove'])]
    private Collection $imagenes;

    #[ORM\ManyToMany(targetEntity: Etiqueta::class, inversedBy: 'servicios')]
    #[ORM\JoinTable(name: 'servicio_etiquetas')]
    #[ORM\JoinColumn(name: 'id_servicio', referencedColumnName: 'id_servicio')]
    #[ORM\InverseJoinColumn(name: 'id_etiqueta', referencedColumnName: 'id_etiqueta')]
    private Collection $etiquetas;

    #[ORM\OneToMany(mappedBy: 'servicio', targetEntity: IntercambioServicio::class)]
    private Collection $intercambios;

    public function __construct()
    {
        $this->imagenes = new ArrayCollection();
        $this->etiquetas = new ArrayCollection();
        $this->intercambios = new ArrayCollection();
    }

    public function getIdServicio(): ?int
    {
        return $this->id_servicio;
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

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): self
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getCreditos(): ?int
    {
        return $this->creditos;
    }

    public function setCreditos(int $creditos): self
    {
        $this->creditos = $creditos;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection<int, ImagenServicio>
     */
    public function getImagenes(): Collection
    {
        return $this->imagenes;
    }

    public function addImagen(ImagenServicio $imagen): self
    {
        if (!$this->imagenes->contains($imagen)) {
            $this->imagenes->add($imagen);
            $imagen->setServicio($this);
        }

        return $this;
    }

    public function removeImagen(ImagenServicio $imagen): self
    {
        if ($this->imagenes->removeElement($imagen)) {
            // set the owning side to null (unless already changed)
            if ($imagen->getServicio() === $this) {
                $imagen->setServicio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Etiqueta>
     */
    public function getEtiquetas(): Collection
    {
        return $this->etiquetas;
    }

    public function addEtiqueta(Etiqueta $etiqueta): self
    {
        if (!$this->etiquetas->contains($etiqueta)) {
            $this->etiquetas->add($etiqueta);
        }

        return $this;
    }

    public function removeEtiqueta(Etiqueta $etiqueta): self
    {
        $this->etiquetas->removeElement($etiqueta);

        return $this;
    }

    /**
     * @return Collection<int, IntercambioServicio>
     */
    public function getIntercambios(): Collection
    {
        return $this->intercambios;
    }

    public function addIntercambio(IntercambioServicio $intercambio): self
    {
        if (!$this->intercambios->contains($intercambio)) {
            $this->intercambios->add($intercambio);
            $intercambio->setServicio($this);
        }

        return $this;
    }

    public function removeIntercambio(IntercambioServicio $intercambio): self
    {
        if ($this->intercambios->removeElement($intercambio)) {
            // set the owning side to null (unless already changed)
            if ($intercambio->getServicio() === $this) {
                $intercambio->setServicio(null);
            }
        }

        return $this;
    }
}