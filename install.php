<?php
// +-----------------------------------------------------------------------+
// | This file is part of Piwigo.                                          |
// |                                                                       |
// | For copyright and license information, please view the COPYING.txt    |
// | file that was distributed with this source code.                      |
// +-----------------------------------------------------------------------+

use Webmozart\PathUtil\Path;

require_once __DIR__.'/include/autoload.php';
require_once __DIR__.'/include/functions_vendi.php';
fix_global_variables();

define('DEFAULT_PREFIX_TABLE', 'piwigo_');

$prefixeTable = isset($_POST['install']) ? $_POST['prefix'] : DEFAULT_PREFIX_TABLE;

require_once Path::join(PHPWG_ROOT_PATH, 'include', 'config_default.inc.php');

//require PHPWG_ROOT_PATH . '/include/config_default.inc.php';
if (file_exists(Path::join(PHPWG_ROOT_PATH, '/local/config/config.inc.php'))) {
    require Path::join(PHPWG_ROOT_PATH, '/local/config/config.inc.php');
}
defined('PWG_LOCAL_DIR') or define('PWG_LOCAL_DIR', 'local/');

require PHPWG_ROOT_PATH.'/include/functions.inc.php';
require PHPWG_ROOT_PATH.'/include/template.class.php';

// download database config file if exists
check_input_parameter('dl', $_GET, false, '/^[a-f0-9]{32}$/');

// TODO: This looks weird
if (!empty($_GET['dl']) && file_exists(PHPWG_ROOT_PATH.$conf['data_location'].'pwg_'.$_GET['dl'])) {
    $filename = PHPWG_ROOT_PATH.$conf['data_location'].'pwg_'.$_GET['dl'];
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="database.inc.php"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.filesize($filename));
    echo file_get_contents($filename);
    unlink($filename);
    exit();
}

// Obtain various vars
$dbhost = (!empty($_POST['dbhost'])) ? $_POST['dbhost'] : 'localhost';
$dbuser = (!empty($_POST['dbuser'])) ? $_POST['dbuser'] : '';
$dbpasswd = (!empty($_POST['dbpasswd'])) ? $_POST['dbpasswd'] : '';
$dbname = (!empty($_POST['dbname'])) ? $_POST['dbname'] : '';

// dblayer
if (extension_loaded('mysqli')) {
    $dblayer = 'mysqli';
} else {
    // We only support mysqli
    fatal_error('PHP extension "mysqli" is not loaded');
}

$admin_name = (!empty($_POST['admin_name'])) ? $_POST['admin_name'] : '';
$admin_pass1 = (!empty($_POST['admin_pass1'])) ? $_POST['admin_pass1'] : '';
$admin_pass2 = (!empty($_POST['admin_pass2'])) ? $_POST['admin_pass2'] : '';
$admin_mail = (!empty($_POST['admin_mail'])) ? $_POST['admin_mail'] : '';

$is_newsletter_subscribe = true;
if (isset($_POST['install'])) {
    $is_newsletter_subscribe = isset($_POST['newsletter_subscribe']);
}

$infos = array();
$errors = array();

$config_file = Path::join(PHPWG_ROOT_PATH, PWG_LOCAL_DIR, '/config/database.inc.php');
if (file_exists($config_file)) {
    require_once $config_file;
    // Is Piwigo already installed ?
    if (defined("PHPWG_INSTALLED")) {
        die('Piwigo is already installed');
    }
}

require_once PHPWG_ROOT_PATH.'/include/constants.php';
require_once PHPWG_ROOT_PATH.'/admin/include/functions.php';

$languages = new languages('utf-8');

if (isset($_GET['language'])) {
    $language = strip_tags($_GET['language']);

    if (!in_array($language, array_keys($languages->fs_languages))) {
        $language = PHPWG_DEFAULT_LANGUAGE;
    }
} else {
    $language = 'en_UK';
    // Try to get browser language
    foreach ($languages->fs_languages as $language_code => $fs_language) {
        if (substr($language_code, 0, 2) == @substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2)) {
            $language = $language_code;
            break;
        }
    }
}


switch ($language) {
    case 'fr_FR':
        define('PHPWG_DOMAIN', 'fr.piwigo.org');
        break;
    case 'it_IT':
        define('PHPWG_DOMAIN', 'it.piwigo.org');
        break;
    case 'de_DE':
        define('PHPWG_DOMAIN', 'de.piwigo.org');
        break;
    case 'es_ES':
        define('PHPWG_DOMAIN', 'es.piwigo.org');
        break;
    case 'pl_PL':
        define('PHPWG_DOMAIN', 'pl.piwigo.org');
        break;
    case 'zh_CN':
        define('PHPWG_DOMAIN', 'cn.piwigo.org');
        break;
    case 'ru_RU':
        define('PHPWG_DOMAIN', 'ru.piwigo.org');
        break;
    case 'nl_NL':
        define('PHPWG_DOMAIN', 'nl.piwigo.org');
        break;
    case 'tr_TR':
        define('PHPWG_DOMAIN', 'tr.piwigo.org');
        break;
    case 'da_DK':
        define('PHPWG_DOMAIN', 'da.piwigo.org');
        break;
    case 'pt_BR':
        define('PHPWG_DOMAIN', 'br.piwigo.org');
        break;
    default:
        define('PHPWG_DOMAIN', 'piwigo.org');
}

define('PHPWG_URL', 'https://'.PHPWG_DOMAIN);

load_language('common.lang', '', array('language' => $language, 'target_charset' => 'utf-8'));
load_language('admin.lang', '', array('language' => $language, 'target_charset' => 'utf-8'));
load_language('install.lang', '', array('language' => $language, 'target_charset' => 'utf-8'));

header('Content-Type: text/html; charset=UTF-8');
//------------------------------------------------- check php version
if (version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '<')) {
    // include(PHPWG_ROOT_PATH.'install/php5_apache_configuration.php'); // to remove, with all its related content
    $errors[] = l10n('PHP version %s required (you are running on PHP %s)', REQUIRED_PHP_VERSION, PHP_VERSION);
}

//----------------------------------------------------- template initialization
$template = new Template(Path::join(PHPWG_ROOT_PATH, '/admin/themes'), 'clear');
$template->set_filenames(array('install' => 'install.tpl'));
if (!isset($step)) {
    $step = 1;
}
//---------------------------------------------------------------- form analyze
require PHPWG_ROOT_PATH.'/include/dblayer/functions_'.$dblayer.'.inc.php';
require PHPWG_ROOT_PATH.'/admin/include/functions_install.inc.php';
require PHPWG_ROOT_PATH.'/admin/include/functions_upgrade.php';

if (isset($_POST['install'])) {
    install_db_connect($infos, $errors);
    pwg_db_check_charset();

    $webmaster = trim(preg_replace('/\s{2,}/', ' ', $admin_name));
    if (empty($webmaster)) {
        $errors[] = l10n('enter a login for webmaster');
    } else {
        if (preg_match('/[\'"]/', $webmaster)) {
            $errors[] = l10n('webmaster login can\'t contain characters \' or "');
        }
    }
    if ($admin_pass1 != $admin_pass2 || empty($admin_pass1)) {
        $errors[] = l10n('please enter your password again');
    }
    if (empty($admin_mail)) {
        $errors[] = l10n('mail address must be like xxx@yyy.eee (example : jack@altern.org)');
    } else {
        $error_mail_address = validate_mail_address(null, $admin_mail);
        if (!empty($error_mail_address)) {
            $errors[] = $error_mail_address;
        }
    }

    if (count($errors) == 0) {
        $step = 2;
        $file_content = '<?php
$conf[\'dblayer\'] = \''.$dblayer.'\';
$conf[\'db_base\'] = \''.$dbname.'\';
$conf[\'db_user\'] = \''.$dbuser.'\';
$conf[\'db_password\'] = \''.$dbpasswd.'\';
$conf[\'db_host\'] = \''.$dbhost.'\';

$prefixeTable = \''.$prefixeTable.'\';

define(\'PHPWG_INSTALLED\', true);
define(\'PWG_CHARSET\', \'utf-8\');
define(\'DB_CHARSET\', \'utf8\');
define(\'DB_COLLATE\', \'\');

?'.'>';

        @umask(0111);
        // writing the configuration file
        if (!($fp = @fopen($config_file, 'w'))) {
            // make sure nobody can list files of _data directory
            secure_directory(PHPWG_ROOT_PATH.$conf['data_location']);

            $tmp_filename = md5(uniqid(time()));
            $fh = @fopen(PHPWG_ROOT_PATH.$conf['data_location'].'pwg_'.$tmp_filename, 'w');
            @fputs($fh, $file_content, strlen($file_content));
            @fclose($fh);

            $template->assign(
                array(
                    'config_creation_failed' => true,
                    'config_url' => 'install.php?dl='.$tmp_filename,
                    'config_file_content' => $file_content,
                )
            );
        }
        @fputs($fp, $file_content, strlen($file_content));
        @fclose($fp);

        // tables creation, based on piwigo_structure.sql
        execute_sqlfile(
            Path::join(PHPWG_ROOT_PATH, 'install/piwigo_structure-mysql.sql'),
            DEFAULT_PREFIX_TABLE,
            $prefixeTable,
            'mysql'
        );
        // We fill the tables with basic informations
        execute_sqlfile(
            Path::join(PHPWG_ROOT_PATH, 'install/config.sql'),
            DEFAULT_PREFIX_TABLE,
            $prefixeTable,
            'mysql'
        );

        $query = '
INSERT INTO '.$prefixeTable.'config (param,value,comment) 
   VALUES (\'secret_key\',md5('.pwg_db_cast_to_text(DB_RANDOM_FUNCTION.'()').'),
   \'a secret key specific to the gallery for internal use\');';
        pwg_query($query);

        conf_update_param('piwigo_db_version', get_branch_from_version(PHPWG_VERSION));
        conf_update_param('gallery_title', pwg_db_real_escape_string(l10n('Just another Piwigo gallery')));

        conf_update_param(
            'page_banner',
            '<h1>%gallery_title%</h1>'."\n\n<p>".pwg_db_real_escape_string(l10n('Welcome to my photo gallery')).'</p>'
        );

        // fill languages table, only activate the current language
        $languages->perform_action('activate', $language);

        // fill $conf global array
        load_conf_from_db();

        // PWG_CHARSET is required for building the fs_themes array in the
        // themes class
        if (!defined('PWG_CHARSET')) {
            define('PWG_CHARSET', 'utf-8');
        }
        activate_core_themes();
        activate_core_plugins();

        $insert = array(
            'id' => 1,
            'galleries_url' => PHPWG_ROOT_PATH.'galleries/',
        );
        mass_inserts(SITES_TABLE, array_keys($insert), array($insert));

        // webmaster admin user
        $inserts = array(
            array(
                'id' => 1,
                'username' => $admin_name,
                'password' => md5($admin_pass1),
                'mail_address' => $admin_mail,
            ),
            array(
                'id' => 2,
                'username' => 'guest',
            ),
        );
        mass_inserts(USERS_TABLE, array_keys($inserts[0]), $inserts);

        create_user_infos(array(1, 2), array('language' => $language));

        // Available upgrades must be ignored after a fresh installation. To
        // make PWG avoid upgrading, we must tell it upgrades have already been
        // made.
        list($dbnow) = pwg_db_fetch_row(pwg_query('SELECT NOW();'));
        define('CURRENT_DATE', $dbnow);
        $datas = array();
        foreach (get_available_upgrade_ids() as $upgrade_id) {
            $datas[] = array(
                'id' => $upgrade_id,
                'applied' => CURRENT_DATE,
                'description' => 'upgrade included in installation',
            );
        }
        mass_inserts(
            UPGRADE_TABLE,
            array_keys($datas[0]),
            $datas
        );

        if ($is_newsletter_subscribe) {
            fetchRemote(
                get_newsletter_subscribe_base_url($language).$admin_mail,
                $result,
                array(),
                array('origin' => 'installation')
            );

            conf_update_param('show_newsletter_subscription', 'false');
        }
    }
}

//------------------------------------------------------ start template output
$languages_options = [];
foreach ($languages->fs_languages as $language_code => $fs_language) {
    if ($language == $language_code) {
        $template->assign('language_selection', $language_code);
    }
    $languages_options[$language_code] = $fs_language['name'];
}
$template->assign('language_options', $languages_options);

$template->assign(
    array(
        'T_CONTENT_ENCODING' => 'utf-8',
        'RELEASE' => PHPWG_VERSION,
        'F_ACTION' => 'install.php?language='.$language,
        'F_DB_HOST' => $dbhost,
        'F_DB_USER' => $dbuser,
        'F_DB_NAME' => $dbname,
        'F_DB_PREFIX' => $prefixeTable,
        'F_ADMIN' => $admin_name,
        'F_ADMIN_EMAIL' => $admin_mail,
        'EMAIL' => '<span class="adminEmail">'.$admin_mail.'</span>',
        'F_NEWSLETTER_SUBSCRIBE' => $is_newsletter_subscribe,
        'L_INSTALL_HELP' => l10n('Need help ? Ask your question on <a href="%s">Piwigo message board</a>.', PHPWG_URL.'/forum'),
    )
);

//------------------------------------------------------ errors & infos display
if ($step == 1) {
    $template->assign('install', true);
} else {
    $infos[] = l10n('Congratulations, Piwigo installation is completed');

    if (isset($error_copy)) {
        $errors[] = $error_copy;
    } else {
        session_set_save_handler(
            'pwg_session_open',
            'pwg_session_close',
            'pwg_session_read',
            'pwg_session_write',
            'pwg_session_destroy',
            'pwg_session_gc'
        );
        if (function_exists('ini_set')) {
            ini_set('session.use_cookies', $conf['session_use_cookies']);
            ini_set('session.use_only_cookies', $conf['session_use_only_cookies']);
            ini_set('session.use_trans_sid', intval($conf['session_use_trans_sid']));
            ini_set('session.cookie_httponly', 1);
        }
        session_name($conf['session_name']);
        session_set_cookie_params(0, cookie_path());
        register_shutdown_function('session_write_close');

        $user = build_user(1, true);
        log_user($user['id'], false);

        // email notification
        if (isset($_POST['send_credentials_by_mail'])) {
            require PHPWG_ROOT_PATH.'/include/functions_mail.inc.php';

            $keyargs_content = array(
                get_l10n_args('Hello %s,', $admin_name),
                get_l10n_args('Welcome to your new installation of Piwigo!', ''),
                get_l10n_args('', ''),
                get_l10n_args('Here are your connection settings', ''),
                get_l10n_args('', ''),
                get_l10n_args('Link: %s', get_absolute_root_url()),
                get_l10n_args('Username: %s', $admin_name),
                get_l10n_args('Password: ********** (no copy by email)', ''),
                get_l10n_args('Email: %s', $admin_mail),
                get_l10n_args('', ''),
                get_l10n_args('Don\'t hesitate to consult our forums for any help: %s', PHPWG_URL),
            );

            pwg_mail(
                $admin_mail,
                array(
                    'subject' => l10n('Just another Piwigo gallery'),
                    'content' => l10n_args($keyargs_content),
                    'content_format' => 'text/plain',
                )
            );
        }
    }
}
if (count($errors) != 0) {
    $template->assign('errors', $errors);
}

if (count($infos) != 0) {
    $template->assign('infos', $infos);
}

//----------------------------------------------------------- html code display
$template->pparse('install');
