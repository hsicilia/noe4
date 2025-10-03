<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name: 'Usuario')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15, unique: true)]
    private ?string $usuario = null;

    #[ORM\Column(length: 15)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private string $salt = '';

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    private ?string $organizacion = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $tipo = null;

    #[ORM\Column(name: 'lugarDefecto', length: 50, nullable: true)]
    private ?string $lugarDefecto = null;

    #[ORM\Column(name: 'geoLongDefecto', type: 'float', nullable: true)]
    private ?float $geoLongDefecto = 0;

    #[ORM\Column(name: 'geoLatDefecto', type: 'float', nullable: true)]
    private ?float $geoLatDefecto = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $activado = true;

    /**
     * @var Collection<int, Ejemplar>
     */
    #[ORM\OneToMany(targetEntity: Ejemplar::class, mappedBy: 'creadoPor')]
    private Collection $ejemplaresCreados;

    /**
     * @var Collection<int, Ejemplar>
     */
    #[ORM\OneToMany(targetEntity: Ejemplar::class, mappedBy: 'modificadoPor')]
    private Collection $ejemplaresModificados;

    /**
     * @var Collection<int, Captura>
     */
    #[ORM\OneToMany(targetEntity: Captura::class, mappedBy: 'creadoPor')]
    private Collection $capturasCreadas;

    /**
     * @var Collection<int, Captura>
     */
    #[ORM\OneToMany(targetEntity: Captura::class, mappedBy: 'modificadoPor')]
    private Collection $capturasModificadas;

    public function __construct()
    {
        $this->ejemplaresCreados = new ArrayCollection();
        $this->ejemplaresModificados = new ArrayCollection();
        $this->capturasCreadas = new ArrayCollection();
        $this->capturasModificadas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): static
    {
        $this->salt = $salt;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getOrganizacion(): ?string
    {
        return $this->organizacion;
    }

    public function setOrganizacion(string $organizacion): static
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getLugarDefecto(): ?string
    {
        return $this->lugarDefecto;
    }

    public function setLugarDefecto(?string $lugarDefecto): static
    {
        $this->lugarDefecto = $lugarDefecto;

        return $this;
    }

    public function getGeoLongDefecto(): ?float
    {
        return $this->geoLongDefecto;
    }

    public function setGeoLongDefecto(?float $geoLongDefecto): static
    {
        $this->geoLongDefecto = $geoLongDefecto;

        return $this;
    }

    public function getGeoLatDefecto(): ?float
    {
        return $this->geoLatDefecto;
    }

    public function setGeoLatDefecto(?float $geoLatDefecto): static
    {
        $this->geoLatDefecto = $geoLatDefecto;

        return $this;
    }

    public function getActivado(): bool
    {
        return $this->activado;
    }

    public function setActivado(bool $activado): static
    {
        $this->activado = $activado;

        return $this;
    }

    /**
     * @return Collection<int, Ejemplar>
     */
    public function getEjemplaresCreados(): Collection
    {
        return $this->ejemplaresCreados;
    }

    public function addEjemplarCreado(Ejemplar $ejemplar): static
    {
        if (!$this->ejemplaresCreados->contains($ejemplar)) {
            $this->ejemplaresCreados->add($ejemplar);
            $ejemplar->setCreadoPor($this);
        }

        return $this;
    }

    public function removeEjemplarCreado(Ejemplar $ejemplar): static
    {
        if ($this->ejemplaresCreados->removeElement($ejemplar)) {
            if ($ejemplar->getCreadoPor() === $this) {
                $ejemplar->setCreadoPor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ejemplar>
     */
    public function getEjemplaresModificados(): Collection
    {
        return $this->ejemplaresModificados;
    }

    public function addEjemplarModificado(Ejemplar $ejemplar): static
    {
        if (!$this->ejemplaresModificados->contains($ejemplar)) {
            $this->ejemplaresModificados->add($ejemplar);
            $ejemplar->setModificadoPor($this);
        }

        return $this;
    }

    public function removeEjemplarModificado(Ejemplar $ejemplar): static
    {
        if ($this->ejemplaresModificados->removeElement($ejemplar)) {
            if ($ejemplar->getModificadoPor() === $this) {
                $ejemplar->setModificadoPor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Captura>
     */
    public function getCapturasCreadas(): Collection
    {
        return $this->capturasCreadas;
    }

    public function addCapturaCreada(Captura $captura): static
    {
        if (!$this->capturasCreadas->contains($captura)) {
            $this->capturasCreadas->add($captura);
            $captura->setCreadoPor($this);
        }

        return $this;
    }

    public function removeCapturaCreada(Captura $captura): static
    {
        if ($this->capturasCreadas->removeElement($captura)) {
            if ($captura->getCreadoPor() === $this) {
                $captura->setCreadoPor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Captura>
     */
    public function getCapturasModificadas(): Collection
    {
        return $this->capturasModificadas;
    }

    public function addCapturaModificada(Captura $captura): static
    {
        if (!$this->capturasModificadas->contains($captura)) {
            $this->capturasModificadas->add($captura);
            $captura->setModificadoPor($this);
        }

        return $this;
    }

    public function removeCapturaModificada(Captura $captura): static
    {
        if ($this->capturasModificadas->removeElement($captura)) {
            if ($captura->getModificadoPor() === $this) {
                $captura->setModificadoPor(null);
            }
        }

        return $this;
    }

    // UserInterface implementation
    public function getRoles(): array
    {
        // Map tipo to roles based on Constantes class from noe2
        return match($this->tipo) {
            1 => ['ROLE_ADMIN'],
            2 => ['ROLE_OPERADOR_PROPIO'],
            3 => ['ROLE_OPERADOR_EXTERNO'],
            4 => ['ROLE_OPERADOR_EXTERNO_PRUEBAS'],
            5 => ['ROLE_VISITANTE'],
            default => ['ROLE_USER'],
        };
    }

    public function eraseCredentials(): void
    {
        // Nothing to do here
    }

    public function getUserIdentifier(): string
    {
        return $this->usuario;
    }

    public function getTipoString(): string
    {
        return match($this->tipo) {
            1 => 'administrador',
            2 => 'operador_propio',
            3 => 'operador_externo',
            4 => 'operador_externo_pruebas',
            5 => 'visitante',
            default => 'desconocido',
        };
    }

    public function __toString(): string
    {
        return $this->nombre ?? '';
    }
}
