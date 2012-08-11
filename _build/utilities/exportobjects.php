<?php
/**
 * ExportChunks
 *
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 *
 * @author Bob Ray
 * 3/27/12
 *
 * ExportChunks is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * ExportChunks is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ExportChunks; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package exportchunks
 */
/**
 * MODx ExportObjects Snippet
 *
 * Description Extracts objects from MODX install to build files for MyComponent
 *
 * @package exportchunks
 *
 */
/* @var $category string */

/* Usage
 *
 * Create a snippet called ExportObjects, paste the code or
 * use this for the snippet code:
 *     return include 'path/to/this/file';
 *
 * Put a tag for the snippet on a page and preview the page
 *
 * elements in &category will be processed (for menus and system settings, ExportObjects
 * will use the 'namespace' field for the match).
 *
 * This file can be run outside of MODX (e.g., in your editor).
 *
 *  With &dryRun=`1`, no files will be written or modified and the output will go to the screen.
 *
 * Typical snippet call (use your package name instead of MyComponent):
 *
    [[!ExportObjects?
        &category=`MyComponent`
        &packageName=`MyComponent`
        &authorName=`Bob Ray`
        &authorEmail=`<bobray@softville.com>`
        &dryRun=`1`
        &createTransportFiles=`1`
        &createObjectFiles=`1`
        &process=`snippets,chunks,plugins,templates,templateVars,menus,systemSettings`
    ]]

 *
 *
 * Object source files will be written to MODX_ASSETS_PATH/mycomponents/{packageNameLower}/core/components/{packageNameLower}/elements/{elementName}/
 *
 * Transport files will be written to MODX_ASSETS_PATH/mycomponents/{packageNameLower}/_build/data/transport.{elementName}.php
 *
 * &transportPath (directory for transport.chunks.php file)
 * defaults to assets/mycomponents/{categoryLower}/_build/data/
 *
 *
*/




/* @var $modx modX */
if (!defined('MODX_CORE_PATH')) {
    $outsideModx = true;
    require_once dirname(dirname(__FILE__)).'/build.config.php';
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx= new modX();
    $modx->initialize('mgr');
    echo '<pre>'; /* used for nice formatting for log messages  */
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');
} else {
    $outsideModx = false;
}
/* These will override settings in the config file */

if ($outsideModx) {
    $scriptProperties = array(

        //'category' => 'notify',
        //'packageName' => 'Notify',
        //'dryRun' => '1',
        //'createTransportFiles' => '1',
        //'createObjectFiles' => '1',
        //'process' => 'plugins,templateVars',
        //'pagetitles' => 'Notify,NotifyPreview', // pagetitles of resources to process
        //'parents' => '', //parents of resources to process
        //'includeParents' => 0,
    );
}

$props =& $scriptProperties;

require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/export.class.php';

$export = new Export($modx,$props);


if ($export->init()) {
    $objects = explode(',', $props['process']);
    foreach ($objects as $object) {
        $export->process(trim($object));
    }

}

$modx->log(modX::LOG_LEVEL_INFO, 'All Finished');



