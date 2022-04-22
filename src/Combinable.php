<?php

/**
 * A Combinable represents a JS or CSS file ready for cobination and minification.
 */
class Combinable
{
    /** @var string */
    public $id;
    /** @var string */
    public $path;
    /** @var string */
    public $version;
    /** @var bool */
    public $is_template;

    /**
     * @param string $id
     * @param string $path
     * @param string $version
     */
    function __construct($id, $path, $version=0)
    {
        $this->id = $id;
        $this->set_path($path);
        $this->version = $version;
        $this->is_template = false;
    }

    /**
     * @param string $path
     */
    function set_path($path)
    {
        if (!empty($path))
            $this->path = $path;
    }

    /**
     * @return bool
     */
    function is_remote()
    {
        return url_is_remote($this->path) || strncmp($this->path, '//', 2)==0;
    }
}