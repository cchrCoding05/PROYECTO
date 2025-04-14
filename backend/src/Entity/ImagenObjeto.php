<?php

namespace App\Entity;

use App\Repository\ImagenObjetoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagenObjetoRepository::class)]
class ImagenObjeto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_imagen = null;

    #[ORM\ManyToOne(inversedBy: 'imagenes')]
    #[ORM\JoinColumn(name: 'id_objeto', referencedColumnName: 'id_objeto', nullable: false)]
    private ?Objeto $objeto = null;

    #[ORM\Column(length: 255)]
    private ?string $url_imagen = null;

    public function getIdImagen(): ?int
    {
        return $this->id_imagen;
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