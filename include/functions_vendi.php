<?php

use Webmozart\PathUtil\Path;

/**
 * @param string $path
 * @return void
 * @depracted 'Too much global stuff'
 */
function require_from_include(string $path): void
{
    include_once Path::join(PHPWG_ROOT_PATH, 'include', $path);
}

function maybe_require_from_local(string $path): bool
{
    $local = defined('PWG_LOCAL_DIR') ? PWG_LOCAL_DIR : 'local';
    $abs = Path::join(PHPWG_ROOT_PATH, $local, $path);
    if (file_exists($abs)) {
        require $abs;

        return true;
    }

    return false;
}

function sanitize_mysql_kv(&$v, $k)
{
    $v = addslashes($v);
}

function fix_global_variables()
{
// @set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

//
// addslashes to vars if magic_quotes_gpc is off this is a security
// precaution to prevent someone trying to break out of a SQL statement.
//
    if (function_exists('get_magic_quotes_gpc') && !@get_magic_quotes_gpc()) {
        if (is_array($_GET)) {
            array_walk_recursive($_GET, 'sanitize_mysql_kv');
        }
        if (is_array($_POST)) {
            array_walk_recursive($_POST, 'sanitize_mysql_kv');
        }
        if (is_array($_COOKIE)) {
            array_walk_recursive($_COOKIE, 'sanitize_mysql_kv');
        }
    }
    if (!empty($_SERVER["PATH_INFO"])) {
        $_SERVER["PATH_INFO"] = addslashes($_SERVER["PATH_INFO"]);
    }
}

function load_php_compat()
{
    foreach (['gzopen',] as $func) {
        if (!function_exists($func)) {
            require_from_include('/php_compat/'.$func.'.php');
        }
    }
}