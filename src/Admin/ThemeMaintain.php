<?php

/**
 * Used to declare maintenance methods of a theme.
 */
class ThemeMaintain
{
    /** @var string $theme_id */
    protected $theme_id;

    /**
     * @param string $id
     */
    function __construct($id)
    {
        $this->theme_id = $id;
    }

    /**
     * @param string $theme_version
     * @param array &$errors - used to return error messages
     */
    function activate($theme_version, &$errors = array())
    {
    }

    function deactivate()
    {
    }

    function delete()
    {
    }
}