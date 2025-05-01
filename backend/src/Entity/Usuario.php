<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_usuario = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $nombre_usuario = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $correo = null;

    #[ORM\Column(length: 255)]
    private ?string $contrasena = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foto_perfil = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $profesion = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_registro;

    #[ORM\Column]
    private ?int $creditos = 100;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Servicio::class)]
    private Collection $servicios;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Objeto::class)]
    private Collection $objetos;

    #[ORM\OneToMany(mappedBy: 'emisor', targetEntity: Mensaje::class)]
    private Collection $mensajes_enviados;

    #[ORM\OneToMany(mappedBy: 'receptor', targetEntity: Mensaje::class)]
    private Collection $mensajes_recibidos;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: TransaccionCredito::class)]
    private Collection $transacciones_credito;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: NegociacionPrecio::class)]
    private Collection $negociaciones_precio;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $valoracion_promedio = null;

    #[ORM\Column]
    private ?int $ventas_realizadas = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    public function __construct()
    {
        $this->fecha_registro = new \DateTimeImmutable();
        $this->servicios = new ArrayCollection();
        $this->objetos = new ArrayCollection();
        $this->mensajes_enviados = new ArrayCollection();
        $this->mensajes_recibidos = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
        $this->negociaciones_precio = new ArrayCollection();
        $this->transacciones_credito = new ArrayCollection();
    }

    public function getId_usuario(): ?int
    {
        return $this->id_usuario;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombre_usuario;
    }

    public function setNombreUsuario(string $nombre_usuario): self
    {
        $this->nombre_usuario = $nombre_usuario;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): self
    {
        $this->contrasena = $contrasena;

        return $this;
    }

    public function getFotoPerfil(): ?string
    {
        return $this->foto_perfil;
    }

    public function setFotoPerfil(?string $foto_perfil): self
    {
        $this->foto_perfil = $foto_perfil;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getProfesion(): ?string
    {
        return $this->profesion;
    }

    public function setProfesion(?string $profesion): self
    {
        $this->profesion = $profesion;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeImmutable
    {
        return $this->fecha_registro;
    }

    public function setFechaRegistro(\DateTimeImmutable $fecha_registro): self
    {
        $this->fecha_registro = $fecha_registro;

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
            $servicio->setUsuario($this);
        }

        return $this;
    }

    public function removeServicio(Servicio $servicio): self
    {
        if ($this->servicios->removeElement($servicio)) {
            // set the owning side to null (unless already changed)
            if ($servicio->getUsuario() === $this) {
                $servicio->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Objeto>
     */
    public function getObjetos(): Collection
    {
        return $this->objetos;
    }

    public function addObjeto(Objeto $objeto): self
    {
        if (!$this->objetos->contains($objeto)) {
            $this->objetos->add($objeto);
            $objeto->setUsuario($this);
        }

        return $this;
    }

    public function removeObjeto(Objeto $objeto): self
    {
        if ($this->objetos->removeElement($objeto)) {
            // set the owning side to null (unless already changed)
            if ($objeto->getUsuario() === $this) {
                $objeto->setUsuario(null);
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
            $valoracion->setUsuario($this);
            $this->actualizarValoracionPromedio();
        }

        return $this;
    }

    public function removeValoracion(Valoracion $valoracion): self
    {
        if ($this->valoraciones->removeElement($valoracion)) {
            // set the owning side to null (unless already changed)
            if ($valoracion->getUsuario() === $this) {
                $valoracion->setUsuario(null);
            }
            $this->actualizarValoracionPromedio();
        }

        return $this;
    }

    /**
     * @return Collection<int, NegociacionPrecio>
     */
    public function getNegociacionesPrecio(): Collection
    {
        return $this->negociaciones_precio;
    }

    public function addNegociacionPrecio(NegociacionPrecio $negociacionPrecio): self
    {
        if (!$this->negociaciones_precio->contains($negociacionPrecio)) {
            $this->negociaciones_precio->add($negociacionPrecio);
            $negociacionPrecio->setUsuario($this);
        }

        return $this;
    }

    public function removeNegociacionPrecio(NegociacionPrecio $negociacionPrecio): self
    {
        if ($this->negociaciones_precio->removeElement($negociacionPrecio)) {
            // set the owning side to null (unless already changed)
            if ($negociacionPrecio->getUsuario() === $this) {
                $negociacionPrecio->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransaccionCredito>
     */
    public function getTransaccionesCredito(): Collection
    {
        return $this->transacciones_credito;
    }

    public function addTransaccionCredito(TransaccionCredito $transaccionCredito): self
    {
        if (!$this->transacciones_credito->contains($transaccionCredito)) {
            $this->transacciones_credito->add($transaccionCredito);
            $transaccionCredito->setUsuario($this);
        }

        return $this;
    }

    public function removeTransaccionCredito(TransaccionCredito $transaccionCredito): self
    {
        if ($this->transacciones_credito->removeElement($transaccionCredito)) {
            // set the owning side to null (unless already changed)
            if ($transaccionCredito->getUsuario() === $this) {
                $transaccionCredito->setUsuario(null);
            }
        }

        return $this;
    }

    public function getValoracionPromedio(): ?float
    {
        return $this->valoracion_promedio;
    }

    public function setValoracionPromedio(?float $valoracion_promedio): self
    {
        $this->valoracion_promedio = $valoracion_promedio;
        return $this;
    }

    public function getVentasRealizadas(): ?int
    {
        return $this->ventas_realizadas;
    }

    public function setVentasRealizadas(int $ventas_realizadas): self
    {
        $this->ventas_realizadas = $ventas_realizadas;
        return $this;
    }

    public function incrementarVentas(): self
    {
        $this->ventas_realizadas++;
        return $this;
    }

    public function actualizarValoracionPromedio(): self
    {
        $totalValoraciones = $this->valoraciones->count();
        if ($totalValoraciones === 0) {
            $this->valoracion_promedio = null;
            return $this;
        }

        $sumaValoraciones = 0;
        foreach ($this->valoraciones as $valoracion) {
            $sumaValoraciones += $valoracion->getPuntuacion();
        }

        $this->valoracion_promedio = round($sumaValoraciones / $totalValoraciones, 1);
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // No es necesario implementar nada aquí
    }

    public function getUserIdentifier(): string
    {
        return $this->correo;
    }

    public function getPassword(): ?string
    {
        return $this->contrasena;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }
}