<?php

namespace App\Entity;


use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $Nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank()]
    private ?string $Description = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    private ?float $Prix = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    private ?int $Stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Photo = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ContenuPanier::class, orphanRemoval: true)]
    private Collection $contenuPaniers;

    public function __construct()
    {
        $this->contenuPaniers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->Prix;
    }

    public function setPrix(float $Prix): static
    {
        $this->Prix = $Prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->Stock;
    }

    public function setStock(int $Stock): static
    {
        $this->Stock = $Stock;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->Photo;
    }

    public function setPhoto(?string $Photo): static
    {
        $this->Photo = $Photo;

        return $this;
    }

    #[ORM\PostRemove]
    public function deletePhoto()
    {
        if ($this->Photo != null) {
            unlink(__DIR__.'/../../public/uploads/'.$this->Photo);
        }
        return true;
    }

    /**
     * @return Collection<int, ContenuPanier>
     */
    public function getContenuPaniers(): Collection
    {
        return $this->contenuPaniers;
    }

    public function addContenuPanier(ContenuPanier $contenuPanier): static
    {
        if (!$this->contenuPaniers->contains($contenuPanier)) {
            $this->contenuPaniers->add($contenuPanier);
            $contenuPanier->setProduit($this);
        }

        return $this;
    }

    public function removeContenuPanier(ContenuPanier $contenuPanier): static
    {
        if ($this->contenuPaniers->removeElement($contenuPanier)) {
            // set the owning side to null (unless already changed)
            if ($contenuPanier->getProduit() === $this) {
                $contenuPanier->setProduit(null);
            }
        }

        return $this;
    }


}
