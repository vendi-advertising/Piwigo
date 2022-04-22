<?php

/**
 * Manages a list of CSS files and combining them in a unique file.
 */
class CssLoader
{
    /** @param Css[] */
    private $registered_css;
    /** @param int used to keep declaration order */
    private $counter;

    function __construct()
    {
        $this->clear();
    }

    function clear()
    {
        $this->registered_css = array();
        $this->counter = 0;
    }

    /**
     * @return Combinable[] array of combined CSS.
     */
    function get_css()
    {
        uasort($this->registered_css, array('CssLoader', 'cmp_by_order'));
        $combiner = new FileCombiner('css', $this->registered_css);
        return $combiner->combine();
    }

    /**
     * Callback for CSS files sorting.
     */
    private static function cmp_by_order($a, $b)
    {
        return $a->order - $b->order;
    }

    /**
     * Adds a new file, if a file with the same $id already exsists, the one with
     * the higher $order or higher $version is kept.
     *
     * @param string $id
     * @param string $path
     * @param string $version
     * @param int $order
     * @param bool $is_template
     */
    function add($id, $path, $version=0, $order=0, $is_template=false)
    {
        if (!isset($this->registered_css[$id]))
        {
            // costum order as an higher impact than declaration order
            $css = new Css($id, $path, $version, $order*1000+$this->counter);
            $css->is_template = $is_template;
            $this->registered_css[$id] = $css;
            $this->counter++;
        }
        else
        {
            $css = $this->registered_css[$id];
            if ($css->order<$order*1000 || version_compare($css->version, $version)<0)
            {
                unset($this->registered_css[$id]);
                $this->add($id, $path, $version, $order, $is_template);
            }
        }
    }
}