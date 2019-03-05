<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CARepository")
 * @ExclusionPolicy("all")
 * @Hateoas\Relation(
 *     "orders",
 *     embedded = @Hateoas\Embedded("expr(object.getOrders())")
 *)
 *
 */
class CA
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Expose
     */
    private $userId;

    /**
     * @ORM\Column(type="float")
     * @Expose
     */
    private $total;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\DateTime(format="Y-m-d")
     * @Expose
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Orders", mappedBy="CA")
     * @Type("array<App\Entity\Orders>")
     */
    private $orders;

    /**
     * @ORM\Column(type="date")
     */
    private $updatedAt;

    public function __construct($userId, $date)
    {
        $this->orders = new ArrayCollection();
        $this->userId = $userId;
        $this->date = $date;
        $this->updatedAt = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|Orders[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): self
    {
        if (!$this->orders->contains($order) && $this->userId == $order->getUserId()) {
            $this->orders[] = $order;
            $order->setCA($this);
            $this->total += $order->getTotal();
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getCA() === $this) {
                $order->setCA(null);
            }
            $this->total -= $order->getTotal();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
