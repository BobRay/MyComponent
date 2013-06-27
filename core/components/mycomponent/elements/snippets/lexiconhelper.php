<?php
/**
 * LexiconHelper
 * Copyright 2012-2013 Bob Ray
 *
 * LexiconHelper is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * LexiconHelper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * LexiconHelper; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package lexiconhelper
 * @author Bob Ray <http://bobsguides.com>
 
 *
 * Description: The LexiconHelper snippet identifies lexicon strings
 * in code and checks them against strings in a language file.
 *
 * You must use single quotes and no spaces.
 *
 * Output can be pasted into language file for editing.
 * ToDo: More info here (~~ option, rewrite files options)
 * /

/*

  Modified: June, 2012

   
  Properties:
    @property code_path  - (required) Path to directory with code
         file. Should end in a slash.
         {core_path} and {assets_path} will be translated.

    @property code_file - (required) name of code file to be analyzed.

    @property language_path - (required) Path to directory with code
         file. Should end in a slash.
         {core_path} and {assets_path} will be translated.

    @property language_file - (optional) Path to language file.
         Default: default.inc.php

    @property language - (optional) Two-letter language code identifying
         language file to process.
         Default: en
    @property manager_language - (optional) Two-letter language code
         to use in error messages and reports. Use only to override manager
         language.
         Default: manager_language System Setting
*/

/**
 * @package = lexiconhelper
 *
 */

/** Important: All language keys in the code file must be in this form:
 *
 *      $modx->lexicon('language_string_key');
 *      $modx->lexicon("language_string_key");
 *
 * or This form:
 *
 *      $modx->lexicon('language_string_key~~value to use');
 *      $modx->lexicon("language_string_key~~value to use");
 *
 * Use no spaces in the key (the left side).
 *
 *
 * With the first version, LexiconHelper will create a lexicon
 * entry with a blank value.
 *
 * With the second version, LexiconHelper will fill in the value as well.
 *
 * You have the option to rewrite the language file to append the new strings.
 *
 * You have the option to rewrite the code file to remove the description
 * from the lexicon() calls.
 *

*/

/* ToDo:  check System Setting descriptions */
/* ToDo: update version */
/* ToDo: update tutorial */
/* ToDo: lexicon strings in resources and chunks */

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
        die('[lexiconhelper.php] Could not find build.config.php');
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
    if (! $modx->user->hasSessionContext('mgr')) {
        die ('Unauthorized Access');
    }
}

// include 'lexiconhelper.class.php';

$modx->lexicon->load('mycomponent:default');

require_once $modx->getOption('mc.core_path', null, $modx->getOption('core_path') . 'components/mycomponent/') . 'model/mycomponent/lexiconhelper.class.php';
$props = isset($scriptProperties) ? $scriptProperties : array();
$lexiconHelper = new LexiconHelper($modx, $props);
    $lexiconHelper->init($props);
    $lexiconHelper->run();
$output = $lexiconHelper->helpers->getOutput();

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