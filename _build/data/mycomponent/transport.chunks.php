<?php
/**
 * chunks transport file for MyComponent extra
 *
 * Copyright 2012-2025 Bob Ray <https://bobsguides.com>
 * Created on 06-26-2013
 *
 * @package mycomponent
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $chunks */


$chunks = array();

$chunks[1] = $modx->newObject('modChunk');
$chunks[1]->fromArray(array (
  'id' => 1,
  'property_preprocess' => false,
  'name' => 'user.input.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[1]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/user.input.php'));

$chunks[2] = $modx->newObject('modChunk');
$chunks[2]->fromArray(array (
  'id' => 2,
  'property_preprocess' => false,
  'name' => 'tvresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[2]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/tvresolver.php'));

$chunks[3] = $modx->newObject('modChunk');
$chunks[3]->fromArray(array (
  'id' => 3,
  'property_preprocess' => false,
  'name' => 'transportfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[3]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/transportfile.php'));

$chunks[4] = $modx->newObject('modChunk');
$chunks[4]->fromArray(array (
  'id' => 4,
  'property_preprocess' => false,
  'name' => 'tutorial.html.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[4]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/tutorial.html.tpl'));

$chunks[5] = $modx->newObject('modChunk');
$chunks[5]->fromArray(array (
  'id' => 5,
  'property_preprocess' => false,
  'name' => 'resourceresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[5]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/resourceresolver.php'));

$chunks[6] = $modx->newObject('modChunk');
$chunks[6]->fromArray(array (
  'id' => 6,
  'property_preprocess' => false,
  'name' => 'readme.md.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[6]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/readme.md.tpl'));

$chunks[7] = $modx->newObject('modChunk');
$chunks[7]->fromArray(array (
  'id' => 7,
  'property_preprocess' => false,
  'name' => 'readme.txt.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[7]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/readme.txt.tpl'));

$chunks[8] = $modx->newObject('modChunk');
$chunks[8]->fromArray(array (
  'id' => 8,
  'property_preprocess' => false,
  'name' => 'removenewevents.php',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[8]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/removenewevents.php'));

$chunks[9] = $modx->newObject('modChunk');
$chunks[9]->fromArray(array (
  'id' => 9,
  'property_preprocess' => false,
  'name' => 'propertiesfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[9]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/propertiesfile.php'));

$chunks[10] = $modx->newObject('modChunk');
$chunks[10]->fromArray(array (
  'id' => 10,
  'property_preprocess' => false,
  'name' => 'propertysetresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[10]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/propertysetresolver.php'));

$chunks[11] = $modx->newObject('modChunk');
$chunks[11]->fromArray(array (
  'id' => 11,
  'property_preprocess' => false,
  'name' => 'genericresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[11]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/genericresolver.php'));

$chunks[12] = $modx->newObject('modChunk');
$chunks[12]->fromArray(array (
  'id' => 12,
  'property_preprocess' => false,
  'name' => 'genericvalidator.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[12]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/genericvalidator.php'));

$chunks[13] = $modx->newObject('modChunk');
$chunks[13]->fromArray(array (
  'id' => 13,
  'property_preprocess' => false,
  'name' => 'js.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[13]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/js.tpl'));

$chunks[14] = $modx->newObject('modChunk');
$chunks[14]->fromArray(array (
  'id' => 14,
  'property_preprocess' => false,
  'name' => 'license.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[14]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/license.tpl'));

$chunks[15] = $modx->newObject('modChunk');
$chunks[15]->fromArray(array (
  'id' => 15,
  'property_preprocess' => false,
  'name' => 'license.txt.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[15]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/license.txt.tpl'));

$chunks[16] = $modx->newObject('modChunk');
$chunks[16]->fromArray(array (
  'id' => 16,
  'property_preprocess' => false,
  'name' => 'modchunk.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[16]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/modchunk.tpl'));

$chunks[17] = $modx->newObject('modChunk');
$chunks[17]->fromArray(array (
  'id' => 17,
  'property_preprocess' => false,
  'name' => 'modresource.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[17]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/modresource.tpl'));

$chunks[18] = $modx->newObject('modChunk');
$chunks[18]->fromArray(array (
  'id' => 18,
  'property_preprocess' => false,
  'name' => 'modtemplate.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[18]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/modtemplate.tpl'));

$chunks[19] = $modx->newObject('modChunk');
$chunks[19]->fromArray(array (
  'id' => 19,
  'property_preprocess' => false,
  'name' => 'phpfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[19]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/phpfile.php'));

$chunks[20] = $modx->newObject('modChunk');
$chunks[20]->fromArray(array (
  'id' => 20,
  'property_preprocess' => false,
  'name' => 'pluginresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[20]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/pluginresolver.php'));

$chunks[21] = $modx->newObject('modChunk');
$chunks[21]->fromArray(array (
  'id' => 21,
  'property_preprocess' => false,
  'name' => 'build.config.php',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[21]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/build.config.php'));

$chunks[22] = $modx->newObject('modChunk');
$chunks[22]->fromArray(array (
  'id' => 22,
  'property_preprocess' => false,
  'name' => 'build.transport.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[22]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/build.transport.php'));

$chunks[23] = $modx->newObject('modChunk');
$chunks[23]->fromArray(array (
  'id' => 23,
  'property_preprocess' => false,
  'name' => 'categoryresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[23]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/categoryresolver.php'));

$chunks[24] = $modx->newObject('modChunk');
$chunks[24]->fromArray(array (
  'id' => 24,
  'property_preprocess' => false,
  'name' => 'changelog.txt.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[24]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/changelog.txt.tpl'));

$chunks[25] = $modx->newObject('modChunk');
$chunks[25]->fromArray(array (
  'id' => 25,
  'property_preprocess' => false,
  'name' => 'classfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[25]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/classfile.php'));

$chunks[26] = $modx->newObject('modChunk');
$chunks[26]->fromArray(array (
  'id' => 26,
  'property_preprocess' => false,
  'name' => 'css.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[26]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/css.tpl'));

$chunks[27] = $modx->newObject('modChunk');
$chunks[27]->fromArray(array (
  'id' => 27,
  'property_preprocess' => false,
  'name' => 'example.config.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[27]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/example.config.php'));

$chunks[28] = $modx->newObject('modChunk');
$chunks[28]->fromArray(array (
  'id' => 28,
  'property_preprocess' => false,
  'name' => 'mycomponentform.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[28]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/mycomponentform.tpl'));

$chunks[29] = $modx->newObject('modChunk');
$chunks[29]->fromArray(array (
  'id' => 29,
  'property_preprocess' => false,
  'name' => 'cmp.actionfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[29]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.actionfile.php'));

$chunks[30] = $modx->newObject('modChunk');
$chunks[30]->fromArray(array (
  'id' => 30,
  'property_preprocess' => false,
  'name' => 'cmp.changecategory.class.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[30]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.changecategory.class.php'));

$chunks[31] = $modx->newObject('modChunk');
$chunks[31]->fromArray(array (
  'id' => 31,
  'property_preprocess' => false,
  'name' => 'cmp.classfile.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[31]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.classfile.php'));

$chunks[32] = $modx->newObject('modChunk');
$chunks[32]->fromArray(array (
  'id' => 32,
  'property_preprocess' => false,
  'name' => 'cmp.connectorfile.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[32]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.connectorfile.tpl'));

$chunks[33] = $modx->newObject('modChunk');
$chunks[33]->fromArray(array (
  'id' => 33,
  'property_preprocess' => false,
  'name' => 'cmp.controllerhome.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[33]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.controllerhome.tpl'));

$chunks[34] = $modx->newObject('modChunk');
$chunks[34]->fromArray(array (
  'id' => 34,
  'property_preprocess' => false,
  'name' => 'cmp.defaultjs.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[34]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.defaultjs.tpl'));

$chunks[35] = $modx->newObject('modChunk');
$chunks[35]->fromArray(array (
  'id' => 35,
  'property_preprocess' => false,
  'name' => 'cmp.getlist.class.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[35]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.getlist.class.php'));

$chunks[36] = $modx->newObject('modChunk');
$chunks[36]->fromArray(array (
  'id' => 36,
  'property_preprocess' => false,
  'name' => 'cmp.grid.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[36]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.grid.tpl'));

$chunks[37] = $modx->newObject('modChunk');
$chunks[37]->fromArray(array (
  'id' => 37,
  'property_preprocess' => false,
  'name' => 'cmp.home.js.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[37]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.home.js.tpl'));

$chunks[38] = $modx->newObject('modChunk');
$chunks[38]->fromArray(array (
  'id' => 38,
  'property_preprocess' => false,
  'name' => 'cmp.home.panel.js.tpl',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[38]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.home.panel.js.tpl'));

$chunks[39] = $modx->newObject('modChunk');
$chunks[39]->fromArray(array (
  'id' => 39,
  'property_preprocess' => false,
  'name' => 'cmp.mgr.css.tpl',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[39]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.mgr.css.tpl'));

$chunks[40] = $modx->newObject('modChunk');
$chunks[40]->fromArray(array (
  'id' => 40,
  'property_preprocess' => false,
  'name' => 'cmp.processor.class.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[40]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/cmp.processor.class.php'));

$chunks[41] = $modx->newObject('modChunk');
$chunks[41]->fromArray(array (
  'id' => 41,
  'property_preprocess' => false,
  'name' => 'widgetresolver.php',
  'description' => 'Resolver to connect widgets with dashboards',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[41]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/widgetresolver.php'));

$chunks[42] = $modx->newObject('modChunk');
$chunks[42]->fromArray(array (
  'id' => 42,
  'property_preprocess' => false,
  'name' => 'processortpl.php',
  'description' => 'Chunk',
  'properties' => NULL,
), '', true, true);
$chunks[42]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/processortpl.php'));

$chunks[43] = $modx->newObject('modChunk');
$chunks[43]->fromArray(array (
  'id' => 43,
  'property_preprocess' => false,
  'name' => 'dashboardresolver.php',
  'description' => 'Chunk',
  'properties' => 
  array (
  ),
), '', true, true);
$chunks[43]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/dashboardresolver.php.chunk.html'));

return $chunks;
