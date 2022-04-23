<?php

/**
 * class DummyTheme_maintain
 * used when a theme uses the old procedural declaration of maintenance methods
 */
class DummyTheme_maintain extends ThemeMaintain
{
    function activate($theme_version, &$errors = array())
    {
        if (is_callable('theme_activate')) {
            return theme_activate($this->theme_id, $theme_version, $errors);
        }
    }

    function deactivate()
    {
        if (is_callable('theme_deactivate')) {
            return theme_deactivate($this->theme_id);
        }
    }

    function delete()
    {
        if (is_callable('theme_delete')) {
            return theme_delete($this->theme_id);
        }
    }
}