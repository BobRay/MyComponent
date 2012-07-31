<?php


$props =& $scriptProperties;

/* @var $modx modX */
if (!defined('MODX_CORE_PATH')) {
    /* These four are not used. Defined here to avoid PHP notices */
    define ('MODX_BASE_URL', '');
    define ('MODX_MANAGER_URL', '');
    define ('MODX_ASSETS_URL', '');
    define ('MODX_CONNECTORS_URL', '');

    require_once dirname(dirname(__FILE__)).'/build.config.php';
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx= new modX();
    $modx->initialize('mgr');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');
    echo "\n<pre>\n"; /* used for nice formatting for log messages if run in a browser */
}

$scriptProperties = array();

$props =& $scriptProperties;
require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/bootstrap.class.php';
$bootStrap = new Bootstrap($modx,$props);

$bootStrap->init();
$bootStrap->createBasics();
$bootStrap->createCategory();
$bootStrap->createElements();
$bootStrap->createAssetsDirs();

$modx->log(MODX::LOG_LEVEL_INFO,'Finished!');

