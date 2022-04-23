<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="language")
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $version;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    public function __construct(string $id, string $version, string $name)
    {
        $this->id = $id;
        $this->version = $version;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getName(): string
    {
        return $this->name;
    }

}