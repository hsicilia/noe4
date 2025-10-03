<?php

namespace App\Entity;

use App\Repository\EjemplarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EjemplarRepository::class)]
#[ORM\Table(name: 'Ejemplar')]
#[ORM\HasLifecycleCallbacks]
class Ejemplar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'fechaRegistro', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaRegistro = null;

    #[ORM\Column(name: 'fechaBaja', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fechaBaja = null;

    #[ORM\Column(name: 'causaBaja', type: 'smallint', nullable: true)]
    private ?int $causaBaja = null;

    #[ORM\Column(name: 'idMicrochip', length: 100, unique: true, nullable: true)]
    private ?string $idMicrochip = null;

    #[ORM\Column(name: 'idAnilla', length: 100, unique: true, nullable: true)]
    private ?string $idAnilla = null;

    #[ORM\Column(name: 'idOtro', length: 100, unique: true, nullable: true)]
    private ?string $idOtro = null;

    #[ORM\Column(name: 'idOtro2', length: 100, unique: true, nullable: true)]
    private ?string $idOtro2 = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $sexo = null;

    #[ORM\ManyToOne(targetEntity: Especie::class, inversedBy: 'ejemplares')]
    #[ORM\JoinColumn(name: 'especie_id', nullable: false, onDelete: 'CASCADE')]
    private ?Especie $especie = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $recinto = null;

    #[ORM\Column(length: 50)]
    private ?string $lugar = null;

    #[ORM\Column(name: 'geoLong', type: 'float', nullable: true)]
    private ?float $geoLong = null;

    #[ORM\Column(name: 'geoLat', type: 'float', nullable: true)]
    private ?float $geoLat = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $origen = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $documentacion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $progenitor1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $progenitor2 = null;

    #[ORM\Column(name: 'depositoNombre', length: 50, nullable: true)]
    private ?string $depositoNombre = null;

    #[ORM\Column(name: 'depositoDNI', length: 9, nullable: true)]
    private ?string $depositoDNI = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $deposito = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(type: 'boolean')]
    private bool $invasora = false;

    #[ORM\Column(type: 'smallint')]
    private ?int $cites = null;

    #[ORM\Column(type: 'boolean')]
    private bool $peligroso = false;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'ejemplaresCreados')]
    #[ORM\JoinColumn(name: 'creado_usuario_id', onDelete: 'SET NULL')]
    private ?Usuario $creadoPor = null;

    #[ORM\Column(name: 'creadoEl', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $creadoEl = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'ejemplaresModificados')]
    #[ORM\JoinColumn(name: 'modificado_usuario_id', onDelete: 'SET NULL')]
    private ?Usuario $modificadoPor = null;

    #[ORM\Column(name: 'modificadoEl', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modificadoEl = null;

    /**
     * @var Collection<int, Captura>
     */
    #[ORM\OneToMany(targetEntity: Captura::class, mappedBy: 'ejemplar')]
    private Collection $capturas;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path3 = null;

    public function __construct()
    {
        $this->capturas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): static
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getFechaBaja(): ?\DateTimeInterface
    {
        return $this->fechaBaja;
    }

    public function setFechaBaja(?\DateTimeInterface $fechaBaja): static
    {
        $this->fechaBaja = $fechaBaja;

        return $this;
    }

    public function getCausaBaja(): ?int
    {
        return $this->causaBaja;
    }

    public function setCausaBaja(?int $causaBaja): static
    {
        $this->causaBaja = $causaBaja;

        return $this;
    }

    public function getIdMicrochip(): ?string
    {
        return $this->idMicrochip;
    }

    public function setIdMicrochip(?string $idMicrochip): static
    {
        $this->idMicrochip = $idMicrochip;

        return $this;
    }

    public function getIdAnilla(): ?string
    {
        return $this->idAnilla;
    }

    public function setIdAnilla(?string $idAnilla): static
    {
        $this->idAnilla = $idAnilla;

        return $this;
    }

    public function getIdOtro(): ?string
    {
        return $this->idOtro;
    }

    public function setIdOtro(?string $idOtro): static
    {
        $this->idOtro = $idOtro;

        return $this;
    }

    public function getIdOtro2(): ?string
    {
        return $this->idOtro2;
    }

    public function setIdOtro2(?string $idOtro2): static
    {
        $this->idOtro2 = $idOtro2;

        return $this;
    }

    public function getSexo(): ?int
    {
        return $this->sexo;
    }

    public function setSexo(int $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getEspecie(): ?Especie
    {
        return $this->especie;
    }

    public function setEspecie(?Especie $especie): static
    {
        $this->especie = $especie;

        return $this;
    }

    public function getRecinto(): ?string
    {
        return $this->recinto;
    }

    public function setRecinto(?string $recinto): static
    {
        $this->recinto = $recinto;

        return $this;
    }

    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    public function setLugar(string $lugar): static
    {
        $this->lugar = $lugar;

        return $this;
    }

    public function getGeoLong(): ?float
    {
        return $this->geoLong;
    }

    public function setGeoLong(?float $geoLong): static
    {
        $this->geoLong = $geoLong;

        return $this;
    }

    public function getGeoLat(): ?float
    {
        return $this->geoLat;
    }

    public function setGeoLat(?float $geoLat): static
    {
        $this->geoLat = $geoLat;

        return $this;
    }

    public function getOrigen(): ?int
    {
        return $this->origen;
    }

    public function setOrigen(int $origen): static
    {
        $this->origen = $origen;

        return $this;
    }

    public function getDocumentacion(): ?int
    {
        return $this->documentacion;
    }

    public function setDocumentacion(int $documentacion): static
    {
        $this->documentacion = $documentacion;

        return $this;
    }

    public function getProgenitor1(): ?string
    {
        return $this->progenitor1;
    }

    public function setProgenitor1(?string $progenitor1): static
    {
        $this->progenitor1 = $progenitor1;

        return $this;
    }

    public function getProgenitor2(): ?string
    {
        return $this->progenitor2;
    }

    public function setProgenitor2(?string $progenitor2): static
    {
        $this->progenitor2 = $progenitor2;

        return $this;
    }

    public function getDepositoNombre(): ?string
    {
        return $this->depositoNombre;
    }

    public function setDepositoNombre(?string $depositoNombre): static
    {
        $this->depositoNombre = $depositoNombre;

        return $this;
    }

    public function getDepositoDNI(): ?string
    {
        return $this->depositoDNI;
    }

    public function setDepositoDNI(?string $depositoDNI): static
    {
        $this->depositoDNI = $depositoDNI;

        return $this;
    }

    public function getDeposito(): ?string
    {
        return $this->deposito;
    }

    public function setDeposito(?string $deposito): static
    {
        $this->deposito = $deposito;

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

    public function getInvasora(): bool
    {
        return $this->invasora;
    }

    public function setInvasora(bool $invasora): static
    {
        $this->invasora = $invasora;

        return $this;
    }

    public function getCites(): ?int
    {
        return $this->cites;
    }

    public function setCites(int $cites): static
    {
        $this->cites = $cites;

        return $this;
    }

    public function getPeligroso(): bool
    {
        return $this->peligroso;
    }

    public function setPeligroso(bool $peligroso): static
    {
        $this->peligroso = $peligroso;

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

    /**
     * @return Collection<int, Captura>
     */
    public function getCapturas(): Collection
    {
        return $this->capturas;
    }

    public function addCaptura(Captura $captura): static
    {
        if (!$this->capturas->contains($captura)) {
            $this->capturas->add($captura);
            $captura->setEjemplar($this);
        }

        return $this;
    }

    public function removeCaptura(Captura $captura): static
    {
        if ($this->capturas->removeElement($captura)) {
            if ($captura->getEjemplar() === $this) {
                $captura->setEjemplar(null);
            }
        }

        return $this;
    }

    public function getPath1(): ?string
    {
        return $this->path1;
    }

    public function setPath1(?string $path1): static
    {
        $this->path1 = $path1;

        return $this;
    }

    public function getPath2(): ?string
    {
        return $this->path2;
    }

    public function setPath2(?string $path2): static
    {
        $this->path2 = $path2;

        return $this;
    }

    public function getPath3(): ?string
    {
        return $this->path3;
    }

    public function setPath3(?string $path3): static
    {
        $this->path3 = $path3;

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
