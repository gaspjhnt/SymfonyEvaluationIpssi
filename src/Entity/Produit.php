<?php

namespace App\Entity;


use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


// The Product entity represents a product with attributes such as name, description, price, stock 
// and associated photo. It is linked to the CartContent entity to define the relationship between products and cart contents, 
// and manages the physical deletion of photo files associated with products once they have been removed from the database.
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

    #[ORM\OneToOne(mappedBy: 'produit', cascade: ['persist', 'remove'])]
    private ?ContenuPanier $contenuPanier = null;

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

    public function getContenuPanier(): ?ContenuPanier
    {
        return $this->contenuPanier;
    }

    public function setContenuPanier(ContenuPanier $contenuPanier): static
    {
        // set the owning side of the relation if necessary
        if ($contenuPanier->getProduit() !== $this) {
            $contenuPanier->setProduit($this);
        }

        $this->contenuPanier = $contenuPanier;

        return $this;
    }
}
