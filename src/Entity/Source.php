<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SourceRepository::class)
 */
class Source
{
    /**
     * @var int
     *
     * @ORM\Column(name="a", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $a;

    /**
     * @var smallint
     *
     * @ORM\Column(name="b", type="smallint", nullable=false)
     */
    private $b;

    /**
     * @var smallint
     *
     * @ORM\Column(name="c", type="smallint", nullable=false)
     */
    private $c;

    public function getA(): ?int
    {
        return $this->a;
    }

    public function setA(int $a): self
    {
        $this->a = $a;

        return $this;
    }

    public function getB(): ?int
    {
        return $this->b;
    }

    public function setB(int $b): self
    {
        $this->b = $b;

        return $this;
    }

    public function getC(): ?int
    {
        return $this->c;
    }

    public function setC(int $c): self
    {
        $this->c = $c;

        return $this;
    }
}
