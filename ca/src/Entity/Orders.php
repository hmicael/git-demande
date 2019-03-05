<?php
// src/Entity/Orders.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrdersRepository")
 * @ExclusionPolicy("all")
 * @UniqueEntity(fields="orderId", message="Cette commande est deja enregistrer")
 */
class Orders
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @Assert\NotBlank()
     * @Expose
     */
    private $orderId;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Expose
     */
    private $userId;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=3)
     * @Assert\NotBlank()
     * @Expose
     */
    private $total;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\DateTime(format="Y-m-d")
     * @Expose
     */
    private $dateCompleted;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CA", inversedBy="orders")
     */
    private $CA;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
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

    public function getDateCompleted(): ?\DateTimeInterface
    {
        return $this->dateCompleted;
    }

    public function setDateCompleted(\DateTimeInterface $dateCompleted): self
    {
        $this->dateCompleted = $dateCompleted;

        return $this;
    }

    public function getCA(): ?CA
    {
        return $this->CA;
    }

    public function setCA(?CA $CA): self
    {
        $this->CA = $CA;

        return $this;
    }
}
