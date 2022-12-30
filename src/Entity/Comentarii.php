<?php

namespace App\Entity;

use App\Repository\ComentariiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComentariiRepository::class)]
class Comentarii
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nume_comentator = null;

    #[ORM\Column]
    private ?int $id_anunt = null;

    #[ORM\Column(length: 4000)]
    private ?string $comentariu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeComentator(): ?string
    {
        return $this->nume_comentator;
    }

    public function setNumeComentator(string $nume_comentator): self
    {
        $this->nume_comentator = $nume_comentator;

        return $this;
    }

    public function getIdAnunt(): ?int
    {
        return $this->id_anunt;
    }

    public function setIdAnunt(int $id_anunt): self
    {
        $this->id_anunt = $id_anunt;

        return $this;
    }

    public function getComentariu(): ?string
    {
        return $this->comentariu;
    }

    public function setComentariu(string $comentariu): self
    {
        $this->comentariu = $comentariu;

        return $this;
    }
}
