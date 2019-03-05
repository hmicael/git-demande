<?php

namespace App\Entity;

use App\Entity\Personne;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParrainRepository")
 * @ExclusionPolicy("all")
 */
class Parrain extends Personne
{
    /**
     * @Type("string")
     * @Expose
     */
    private $bonus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Filleul", mappedBy="parrain", orphanRemoval=true)
     * @Expose
     */
    private $filleuls;

    public function __construct()
    {
        $this->filleuls = new ArrayCollection();
        $this->bonus = 0;
    }

    /**
     * @return Collection|Filleul[]
     */
    public function getFilleuls(): Collection
    {
        return $this->filleuls;
    }

    public function addFilleul(Filleul $filleul): self
    {
        if (!$this->filleuls->contains($filleul)) {
            $this->filleuls[] = $filleul;
            $filleul->setParrain($this);
        }

        return $this;
    }

    public function removeFilleul(Filleul $filleul): self
    {
        if ($this->filleuls->contains($filleul)) {
            $this->filleuls->removeElement($filleul);
            // set the owning side to null (unless already changed)
            if ($filleul->getParrain() === $this) {
                $filleul->setParrain(null);
            }
        }

        return $this;
    }

    public function getBonus(): ?string
    {
        return $this->bonus;
    }

    public function setBonus(?string $bonus): self
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getDifferentFilleuls()
    {
        $directFilleul = [];
        $indirectFilleul = [];
        foreach ($this->filleuls as $filleul) {
            if ($filleul->getIndirect() == true) {
                $indirectFilleul[] = $filleul;
            } else {
                $directFilleul[] = $filleul;
            }
        }
        return array('direct' => $directFilleul,
            'indirect' => $indirectFilleul
        );
    }
}
