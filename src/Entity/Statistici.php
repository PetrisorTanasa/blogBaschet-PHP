<?php

namespace App\Entity;

use App\Repository\StatisticiVizitatoriRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatisticiVizitatoriRepository::class)]
class Statistici
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sistem = null;

    #[ORM\Column]
    private ?int $count = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSistem(): ?string
    {
        return $this->sistem;
    }

    public function setSistem(string $sistem): self
    {
        $this->sistem = $sistem;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
