<?php

namespace App\Entity;

use App\Entity\Personne;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilleulRepository")
 */
class Filleul extends Personne
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parrain", inversedBy="filleuls")
     */
    private $parrain;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $indirect;

    public function __construct()
    {
        $this->parrain = null;
        $this->indirect = false;
    }

    public function getParrain(): ?Parrain
    {
        return $this->parrain;
    }

    public function setParrain(?Parrain $parrain): self
    {
        $this->parrain = $parrain;

        return $this;
    }

    public function getIndirect(): ?bool
    {
        return $this->indirect;
    }

    public function setIndirect(?bool $indirect): self
    {
        $this->indirect = $indirect;

        return $this;
    }
}
