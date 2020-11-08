<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandRepository::class)
 */
class Command
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="json")
     */
    private $productsInfo = [];

    /**
     * @ORM\Column(type="json")
     */
    private $userInfo = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $methodPayement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProductsInfo(): ?array
    {
        return $this->productsInfo;
    }

    public function setProductsInfo(array $productsInfo): self
    {
        $this->productsInfo = $productsInfo;

        return $this;
    }

    public function getUserInfo(): ?array
    {
        return $this->userInfo;
    }

    public function setUserInfo(array $userInfo): self
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    public function getReference(): ?int
    {
        return $this->reference;
    }

    public function setReference(int $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getMethodPayement(): ?string
    {
        return $this->methodPayement;
    }

    public function setMethodPayement(string $methodPayement): self
    {
        $this->methodPayement = $methodPayement;

        return $this;
    }
}
