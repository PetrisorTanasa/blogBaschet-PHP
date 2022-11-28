<?php

namespace App\Entity;

use App\Repository\StiriRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StiriRepository::class)]
class Stiri
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titlu = null;

    #[ORM\Column(length: 255)]
    private ?string $rezumat = null;

    #[ORM\Column(length: 4000)]
    private ?string $text = null;

    #[ORM\Column(length: 255)]
    private ?string $poza1 = null;

    #[ORM\Column(length: 255)]
    private ?string $poza2 = null;

    #[ORM\Column(length: 255)]
    private ?string $poza3 = null;

    #[ORM\Column(length: 255)]
    private ?string $autor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitlu(): ?string
    {
        return $this->titlu;
    }

    public function setTitlu(string $titlu): self
    {
        $this->titlu = $titlu;

        return $this;
    }

    public function getRezumat(): ?string
    {
        return $this->rezumat;
    }

    public function setRezumat(string $rezumat): self
    {
        $this->rezumat = $rezumat;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getPoza1(): ?string
    {
        return $this->poza1;
    }

    public function setPoza1(string $poza1): self
    {
        $this->poza1 = $poza1;

        return $this;
    }

    public function getPoza2(): ?string
    {
        return $this->poza2;
    }

    public function setPoza2(string $poza2): self
    {
        $this->poza2 = $poza2;

        return $this;
    }

    public function getPoza3(): ?string
    {
        return $this->poza3;
    }

    public function setPoza3(string $poza3): self
    {
        $this->poza3 = $poza3;

        return $this;
    }

    public function getAutor(): ?string
    {
        return $this->autor;
    }

    public function setAutor(string $autor): self
    {
        $this->autor = $autor;

        return $this;
    }
}
