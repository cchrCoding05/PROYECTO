<?php

namespace App\Entity;

use App\Repository\ImagenServicioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagenServicioRepository::class)]
class ImagenServicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_imagen = null;

    #[ORM\ManyToOne(inversedBy: 'imagenes')]
    #[ORM\JoinColumn(name: 'id_servicio', referencedColumnName: 'id_servicio', nullable: false)]
    private ?Servicio $servicio = null;

    #[ORM\Column(length: 255)]
    private ?string $url_imagen = null;

    public function getId_imagen(): ?int
    {
        return $this->id_imagen;
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

    public function getUrlImagen(): ?string
    {
        return $this->url_imagen;
    }

    public function setUrlImagen(string $url_imagen): self
    {
        $this->url_imagen = $url_imagen;

        return $this;
    }
}