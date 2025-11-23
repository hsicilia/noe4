<?php

namespace App\Entity;

use App\Repository\EspecieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EspecieRepository::class)]
#[ORM\Table(name: 'Especie')]
class Especie implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $comun = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $invasora = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $cites = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $peligroso = null;

    /**
     * @var Collection<int, Ejemplar>
     */
    #[ORM\OneToMany(targetEntity: Ejemplar::class, mappedBy: 'especie')]
    private Collection $ejemplares;

    public function __construct()
    {
        $this->ejemplares = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nombre ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getComun(): ?string
    {
        return $this->comun;
    }

    public function setComun(string $comun): static
    {
        $this->comun = $comun;

        return $this;
    }

    public function getInvasora(): ?bool
    {
        return $this->invasora;
    }

    public function setInvasora(?bool $invasora): static
    {
        $this->invasora = $invasora;

        return $this;
    }

    public function getCites(): ?int
    {
        return $this->cites;
    }

    public function setCites(?int $cites): static
    {
        $this->cites = $cites;

        return $this;
    }

    public function getPeligroso(): ?bool
    {
        return $this->peligroso;
    }

    public function setPeligroso(?bool $peligroso): static
    {
        $this->peligroso = $peligroso;

        return $this;
    }

    /**
     * @return Collection<int, Ejemplar>
     */
    public function getEjemplares(): Collection
    {
        return $this->ejemplares;
    }

    public function addEjemplar(Ejemplar $ejemplar): static
    {
        if (! $this->ejemplares->contains($ejemplar)) {
            $this->ejemplares->add($ejemplar);
            $ejemplar->setEspecie($this);
        }

        return $this;
    }

    public function removeEjemplar(Ejemplar $ejemplar): static
    {
        if ($this->ejemplares->removeElement($ejemplar) && $ejemplar->getEspecie() === $this) {
            $ejemplar->setEspecie(null);
        }

        return $this;
    }
}
