<?php

$props =& $scriptProperties;

/* @var $modx modX */
if (!defined('MODX_CORE_PATH')) {
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
    echo '<pre>'; /* used for nice formatting for log messages  */
}
    if (! defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR);
    }
    /* These are not used. Defined here to avoid PHP notices */
    if (! defined('MODX_CORE_PATH')) {


    }



    $scriptProperties = array(
      'ignoreDirs' => array(),
      'noProcess' => array(),
      'componentName' => 'test',
    );
    $props =& $scriptProperties;
    require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/bootstrap.class.php';
    $bootStrap = new Bootstrap($modx,$props);
    $bootStrap->init();

    if (! $bootStrap->copy()) {
        die();
    }

    $bootStrap->renameDirs();

    $modx->log(MODX::LOG_LEVEL_INFO,'Finished renaming. Doing Search and Replace');

    $bootStrap->doSearchReplace();
    $modx->log(MODX::LOG_LEVEL_INFO,'Finished!');

?>