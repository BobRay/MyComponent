<?php

/* Location of config file for current project.
 * Change this when you start work on a different project!!!
 *
 * This assumes that all your project config files are in the
 * assets/mycomponents/mycomponent/_build/config directory
 * if not, change the MYCOMPONENT_ROOT define below.
 */
$configFileName = 'example.config.php';

/* Define the MODX path constants necessary for connecting to your core and other directories.
 * If you have not moved the core, the current values should work.
 * In some cases, you may have to hard-code the full paths */
if (! defined('MODX_CORE_PATH')) {
    define('MODX_CORE_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/core/');
    define('MODX_BASE_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
    define('MODX_MANAGER_PATH', MODX_BASE_PATH . 'manager/');
    define('MODX_CONNECTORS_PATH', MODX_BASE_PATH . 'connectors/');
    define('MODX_ASSETS_PATH', MODX_BASE_PATH . 'assets/');
}

/* This define is used here, AND in the build.transport.php file - edit if necessary */
if (!defined('MYCOMPONENT_ROOT')) {
    define('MYCOMPONENT_ROOT', MODX_ASSETS_PATH . 'mycomponents/mycomponent/' );
}
$configFile = MYCOMPONENT_ROOT . '_build/utilities/config/' . $configFileName;


/* not used -- here to prevent E_NOTICE warnings */
if (!defined('MODX_BASE_URL')) {
    define('MODX_BASE_URL', 'http://localhost/addons/');
    define('MODX_MANAGER_URL', 'http://localhost/addons/manager/');
    define('MODX_ASSETS_URL', 'http://localhost/addons/assets/');
    define('MODX_CONNECTORS_URL', 'http://localhost/addons/connectors/');
}

return require $configFile;
