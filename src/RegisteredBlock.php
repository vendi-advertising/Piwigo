<?php

/**
 * Represents a menu block registered in a BlockManager object.
 */
class RegisteredBlock
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $name;
    /** @var string */
    protected $owner;

    /**
     * @param string $id
     * @param string $name
     * @param string $owner
     */
    public function __construct($id, $name, $owner)
    {
        $this->id = $id;
        $this->name = $name;
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function get_owner()
    {
        return $this->owner;
    }
}