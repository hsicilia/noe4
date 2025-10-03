<?php

namespace App\Entity;

use App\Repository\CapturaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapturaRepository::class)]
#[ORM\Table(name: 'Captura')]
#[ORM\HasLifecycleCallbacks]
class Captura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ejemplar::class, inversedBy: 'capturas')]
    #[ORM\JoinColumn(name: 'ejemplar_id', nullable: false, onDelete: 'CASCADE')]
    private ?Ejemplar $ejemplar = null;

    #[ORM\Column(name: 'tipoCaptura', length: 50)]
    private ?string $tipoCaptura = null;

    #[ORM\Column(name: 'fechaCaptura', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaCaptura = null;

    #[ORM\Column(name: 'horaCaptura', type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $horaCaptura = null;

    #[ORM\Column(name: 'lugarCaptura', length: 100)]
    private ?string $lugarCaptura = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(name: 'fotosURL', type: Types::TEXT, length: 255, nullable: true)]
    private ?string $fotosURL = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'capturasCreadas')]
    #[ORM\JoinColumn(name: 'creado_usuario_id', onDelete: 'SET NULL')]
    private ?Usuario $creadoPor = null;

    #[ORM\Column(name: 'creadoEl', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $creadoEl = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'capturasModificadas')]
    #[ORM\JoinColumn(name: 'modificado_usuario_id', onDelete: 'SET NULL')]
    private ?Usuario $modificadoPor = null;

    #[ORM\Column(name: 'modificadoEl', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modificadoEl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEjemplar(): ?Ejemplar
    {
        return $this->ejemplar;
    }

    public function setEjemplar(?Ejemplar $ejemplar): static
    {
        $this->ejemplar = $ejemplar;

        return $this;
    }

    public function getTipoCaptura(): ?string
    {
        return $this->tipoCaptura;
    }

    public function setTipoCaptura(string $tipoCaptura): static
    {
        $this->tipoCaptura = $tipoCaptura;

        return $this;
    }

    public function getFechaCaptura(): ?\DateTimeInterface
    {
        return $this->fechaCaptura;
    }

    public function setFechaCaptura(\DateTimeInterface $fechaCaptura): static
    {
        $this->fechaCaptura = $fechaCaptura;

        return $this;
    }

    public function getHoraCaptura(): ?\DateTimeInterface
    {
        return $this->horaCaptura;
    }

    public function setHoraCaptura(?\DateTimeInterface $horaCaptura): static
    {
        $this->horaCaptura = $horaCaptura;

        return $this;
    }

    public function getLugarCaptura(): ?string
    {
        return $this->lugarCaptura;
    }

    public function setLugarCaptura(string $lugarCaptura): static
    {
        $this->lugarCaptura = $lugarCaptura;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    public function getFotosURL(): ?string
    {
        return $this->fotosURL;
    }

    public function setFotosURL(?string $fotosURL): static
    {
        $this->fotosURL = $fotosURL;

        return $this;
    }

    public function getCreadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function setCreadoPor(?Usuario $creadoPor): static
    {
        $this->creadoPor = $creadoPor;

        return $this;
    }

    public function getCreadoEl(): ?\DateTimeInterface
    {
        return $this->creadoEl;
    }

    public function setCreadoEl(?\DateTimeInterface $creadoEl): static
    {
        $this->creadoEl = $creadoEl;

        return $this;
    }

    public function getModificadoPor(): ?Usuario
    {
        return $this->modificadoPor;
    }

    public function setModificadoPor(?Usuario $modificadoPor): static
    {
        $this->modificadoPor = $modificadoPor;

        return $this;
    }

    public function getModificadoEl(): ?\DateTimeInterface
    {
        return $this->modificadoEl;
    }

    public function setModificadoEl(\DateTimeInterface $modificadoEl): static
    {
        $this->modificadoEl = $modificadoEl;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function actualizarTimestamps(): void
    {
        $this->modificadoEl = new \DateTime();

        if ($this->creadoEl === null) {
            $this->creadoEl = new \DateTime();
        }
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
