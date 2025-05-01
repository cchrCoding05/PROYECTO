<?php

namespace App\Entity;

use App\Repository\ObjetoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\Choice(choices: [1, 2, 3], message: 'El estado debe ser 1 (Disponible), 2 (Reservado) o 3 (Intercambiado)')]
    private ?int $estado = null;

    public const ESTADO_DISPONIBLE = 1;
    public const ESTADO_RESERVADO = 2;
    public const ESTADO_INTERCAMBIADO = 3;

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
        $this->estado = self::ESTADO_DISPONIBLE;
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

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(int $estado): self
    {
        if (!in_array($estado, [self::ESTADO_DISPONIBLE, self::ESTADO_RESERVADO, self::ESTADO_INTERCAMBIADO])) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        $this->estado = $estado;
        return $this;
    }

    public function marcarComoDisponible(): self
    {
        return $this->setEstado(self::ESTADO_DISPONIBLE);
    }

    public function marcarComoReservado(): self
    {
        return $this->setEstado(self::ESTADO_RESERVADO);
    }

    public function marcarComoIntercambiado(): self
    {
        $this->setEstado(self::ESTADO_INTERCAMBIADO);
        // Marcar todos los intercambios activos como completados
        foreach ($this->intercambios as $intercambio) {
            if (!$intercambio->getFechaCompletado()) {
                $intercambio->marcarComoCompletado();
            }
        }
        return $this;
    }

    public function estaDisponible(): bool
    {
        return $this->estado === self::ESTADO_DISPONIBLE;
    }

    public function estaReservado(): bool
    {
        return $this->estado === self::ESTADO_RESERVADO;
    }

    public function estaIntercambiado(): bool
    {
        return $this->estado === self::ESTADO_INTERCAMBIADO;
    }

    public function getEstadoTexto(): string
    {
        return match($this->estado) {
            self::ESTADO_DISPONIBLE => 'Disponible',
            self::ESTADO_RESERVADO => 'Reservado',
            self::ESTADO_INTERCAMBIADO => 'Intercambiado',
            default => 'Desconocido'
        };
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