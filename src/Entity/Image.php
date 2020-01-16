<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $lunId;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFloorPlan;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getLunId(): int
    {
        return $this->lunId;
    }

    /**
     * @param int $lunId
     * @return Image
     */
    public function setLunId(int $lunId): self
    {
        $this->lunId = $lunId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFloorPlan(): bool
    {
        return $this->isFloorPlan;
    }

    /**
     * @param bool $isFloorPlan
     * @return Image
     */
    public function setIsFloorPlan(bool $isFloorPlan): self
    {
        $this->isFloorPlan = $isFloorPlan;

        return $this;
    }
}