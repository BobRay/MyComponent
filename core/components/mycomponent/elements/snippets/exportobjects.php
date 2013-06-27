<?php
/**
 * ExportObjects script for MyComponent Extra
 *
 * Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
 *
 * @author Bob Ray
 * 3/27/12
 *
 * ExportObjects is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * ExportObjects is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ExportObjects; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package exportobjects
 */
/**
 * MODx ExportObjects script
 *
 * Description:
 * ------------
 * Extracts objects (resources, chunks, snippets, etc.) from a MODX
 * install and creates code and transport build files for
 * MyComponent to use in creating a transport package
 *
 * Warning: Will overwrite code files for resources and elements
 * (except static elements) if dryRun is not set.
 *
 * Warning: Will overwrite transport files, resolvers, and
 * properties for processed elements and resources if dryRun
 * is not set.
 *
 * @package exportobjects
 *
 */
/* @var $category string */

/*
 *
 * Object source files will be written to
 *  MODX_ASSETS_PATH/mycomponents/{packageNameLower}/core/components/
 * {packageNameLower}/elements/{elementName}/
 *
 * Transport files will be written to MODX_ASSETS_PATH/mycomponents/
 * {packageNameLower}/_build/data/transport.{elementName}.php
 *
 * &transportPath (directory for transport.chunks.php file)
 * defaults to assets/mycomponents/{categoryLower}/_build/data/
 *
 *
 */

/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);
$mem_usage = memory_get_usage();

/* @var $modx modX */

$cliMode = false;

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

        $cliMode = true;
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
// include 'mycomponentproject.class.php';

$modx->lexicon->load('mycomponent:default');

require_once $modx->getOption('mc.core_path', null, $modx->getOption('core_path') . 'components/mycomponent/') . 'model/mycomponent/mycomponentproject.class.php';

$project = new MyComponentProject($modx);
$props = isset($scriptProperties) ? $scriptProperties : array();
$project->init($props);
$project->exportComponent(false);

$output = $project->helpers->getOutput();

// echo print_r(ObjectAdapter::$myObjects, true);
$output .= "\n\n" . $modx->lexicon('mc_initial_memory_used') . ': ' . round($mem_usage / 1048576, 2) . ' ' .
    $modx->lexicon('mc_megabytes');
$mem_usage = memory_get_usage();
$peak_usage = memory_get_peak_usage(true);
$output .= "\n" . $modx->lexicon('mc_final_memory_used')
    . ': ' . round($mem_usage / 1048576, 2) . ' ' .
    $modx->lexicon('mc_megabytes');
$output .= "\n" . $modx->lexicon('mc_peak_memory_used')
    . ': ' . round($peak_usage / 1048576, 2) . ' ' .
    $modx->lexicon('mc_megabytes');
/* report how long it took */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);
$output .= "\n" . $modx->lexicon('mc_total_time') .
    ': ' . $totalTime;

if ($cliMode) {
    echo $output;
} else {
    return $output;
}
