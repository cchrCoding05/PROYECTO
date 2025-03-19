<?php

namespace App\Entity;

use App\Repository\ObjetoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjetoRepository::class)]
class Objeto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_objeto = null;

    #[ORM\ManyToOne(inversedBy: 'objetos')]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id_usuario', nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 100)]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text')]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?int $creditos = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion;

    #[ORM\OneToMany(mappedBy: 'objeto', targetEntity: ImagenObjeto::class, cascade: ['persist', 'remove'])]
    private Collection $imagenes;

    #[ORM\OneToMany(mappedBy: 'objeto', targetEntity: IntercambioObjeto::class)]
    private Collection $intercambios;

    public function __construct()
    {
        $this->fecha_creacion = new \DateTimeImmutable();
        $this->imagenes = new ArrayCollection();
        $this->intercambios = new ArrayCollection();
    }

    public function getId_objeto(): ?int
    {
        return $this->id_objeto;
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

    public function getCreditos(): ?int
    {
        return $this->creditos;
    }

    public function setCreditos(int $creditos): self
    {
        $this->creditos = $creditos;

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

    /**
     * @return Collection<int, ImagenObjeto>
     */
    public function getImagenes(): Collection
    {
        return $this->imagenes;
    }

    public function addImagen(ImagenObjeto $imagen): self
    {
        if (!$this->imagenes->contains($imagen)) {
            $this->imagenes->add($imagen);
            $imagen->setObjeto($this);
        }

        return $this;
    }

    public function removeImagen(ImagenObjeto $imagen): self
    {
        if ($this->imagenes->removeElement($imagen)) {
            // set the owning side to null (unless already changed)
            if ($imagen->getObjeto() === $this) {
                $imagen->setObjeto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IntercambioObjeto>
     */
    public function getIntercambios(): Collection
    {
        return $this->intercambios;
    }

    public function addIntercambio(IntercambioObjeto $intercambio): self
    {
        if (!$this->intercambios->contains($intercambio)) {
            $this->intercambios->add($intercambio);
            $intercambio->setObjeto($this);
        }

        return $this;
    }

    public function removeIntercambio(IntercambioObjeto $intercambio): self
    {
        if ($this->intercambios->removeElement($intercambio)) {
            // set the owning side to null (unless already changed)
            if ($intercambio->getObjeto() === $this) {
                $intercambio->setObjeto(null);
            }
        }

        return $this;
    }
}