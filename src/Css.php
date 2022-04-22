<?php

/**
 * Implementation of Combinable for CSS files.
 */
final class Css extends Combinable
{
    /** @var int */
    public $order;

    /**
     * @param string $id
     * @param string $path
     * @param string $version
     * @param int $order
     */
    function __construct($id, $path, $version=0, $order=0)
    {
        parent::__construct($id, $path, $version);
        $this->order = $order;
    }
}