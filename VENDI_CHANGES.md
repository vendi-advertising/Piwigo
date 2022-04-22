# Changes made by Vendi

* `PHPWG_ROOT_PATH` is now defined in `/include/autoload.php` once, and **without** a trailing slash
* Started using composer
* Smarty moved to composer, locked current at 3.1.39
    * NOTE: In `\include\smarty\libs\sysplugins\smarty_internal_undefined.php` there's some code at the bottom that is
      patched in Piwigo that is not being brought over 