<?php
/**
 * snippets transport file for MyComponent extra
 *
 * Copyright 2012-2017 Bob Ray <https://bobsguides.com>
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
/* @var xPDOObject[] $snippets */


$snippets = array();

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array (
  'id' => 1,
  'property_preprocess' => false,
  'name' => 'LexiconHelper',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[1]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/lexiconhelper.php'));

$snippets[2] = $modx->newObject('modSnippet');
$snippets[2]->fromArray(array (
  'id' => 2,
  'property_preprocess' => false,
  'name' => 'ExportObjects',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[2]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/exportobjects.php'));

$snippets[3] = $modx->newObject('modSnippet');
$snippets[3]->fromArray(array (
  'id' => 3,
  'property_preprocess' => false,
  'name' => 'Bootstrap',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[3]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/bootstrap.php'));

$snippets[4] = $modx->newObject('modSnippet');
$snippets[4]->fromArray(array (
  'id' => 4,
  'property_preprocess' => false,
  'name' => 'MyComponent',
  'description' => '',
  'properties' => 
  array (
  ),
), '', true, true);
$snippets[4]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/mycomponent.php'));

$snippets[5] = $modx->newObject('modSnippet');
$snippets[5]->fromArray(array (
  'id' => 5,
  'property_preprocess' => false,
  'name' => 'ImportObjects',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[5]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/importobjects.php'));

$snippets[6] = $modx->newObject('modSnippet');
$snippets[6]->fromArray(array (
  'id' => 6,
  'property_preprocess' => false,
  'name' => 'RemoveObjects',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[6]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/removeobjects.php'));

$snippets[7] = $modx->newObject('modSnippet');
$snippets[7]->fromArray(array (
  'id' => 7,
  'property_preprocess' => false,
  'name' => 'Build',
  'description' => '',
  'properties' => NULL,
), '', true, true);
$snippets[7]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/build.php'));

$snippets[8] = $modx->newObject('modSnippet');
$snippets[8]->fromArray(array (
  'id' => 8,
  'property_preprocess' => false,
  'name' => 'CheckProperties',
  'description' => '',
  'properties' => 
  array (
  ),
), '', true, true);
$snippets[8]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/checkproperties.php'));

return $snippets;
