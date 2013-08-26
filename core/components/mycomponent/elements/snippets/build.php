<?php
/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);
$mem_usage = memory_get_usage();

/* @var $modx modX */
if (! class_exists('PropHelper')) {
    class PropHelper {

        public function __construct(&$modx) {
            $this->modx =& $modx;

        }

        public function getProps($configPath) {
            $properties = @include $configPath;
            return $properties;
        }

        public function sendLog($level, $message) {
            $msg = '';
            if ($level == modX::LOG_LEVEL_ERROR) {
                $msg .= 'ERROR -- ';
            }
            $msg .= $message;
            $msg .= "\n";
            if (php_sapi_name() != 'cli') {
                $msg = nl2br($msg);
            }
            echo $msg;
        }
    }
}


if (!defined('MODX_CORE_PATH')) {
    $path1 = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/build.config.php';
    if (file_exists($path1)) {
        include $path1;
    } else {
        $path2 = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config/config.inc.php';
        if (file_exists($path2)) {
            include($path2);
        }
    }
    if (!defined('MODX_CORE_PATH')) {
        session_write_close();
        die('[bootstrap.php] Could not find build.config.php');
    }
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx = new modX();
    /* Initialize and set up logging */
    $modx->initialize('mgr');
    $modx->getService('error', 'error.modError', '', '');
    $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
    $modx->setLogTarget(XPDO_CLI_MODE
        ? 'ECHO'
        : 'HTML');

    /* This section will only run when operating outside of MODX */
    if (php_sapi_name() == 'cli') {
        /* Set $modx->user and $modx->resource to avoid
         * other people's plugins from crashing us */
        $modx->getRequest();
        $homeId = $modx->getOption('site_start');
        $homeResource = $modx->getObject('modResource', $homeId);

        if ($homeResource instanceof modResource) {
            $modx->resource = $homeResource;
        } else {
            echo "\nNo Resource\n";
        }

    }

} else {
    if (!$modx->user->hasSessionContext('mgr')) {
        die ('Unauthorized Access');
    }
}

$modx->lexicon->load('mycomponent:default');
// include build.transport.php

$buildPath =  $modx->getOption('mc.root', null, $modx->getOption('core_path') . 'components/mycomponent/') . '_build/';

$file = 'config/current.project.php';

@include $buildPath . $file;

if (! isset($currentProject)) {
    die ('Could not find current project file at: ' . $buildPath . $file);
}
$configPath = $buildPath . 'config/' . $currentProject . '.config.php';

if (! file_exists($configPath)) {
    session_write_close();
    die('Could not find project config file at: ' . $configPath);
} else {
    $helper = new PropHelper($modx);
    $props = $helper->getProps($configPath);
}

if (! is_array($props)) {
    session_write_close();
    die('Project Config file is corrupt');
}

$transportPath = $props['targetRoot'] . '_build/build.transport.php';

if (! file_exists($transportPath)) {
    session_write_close();
    die('Could not find build.transport.php at: ' . $transportPath);
}
unset ($props);
return include $transportPath;