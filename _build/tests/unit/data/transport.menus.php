<?php
/**
 * menus transport file for Example extra
 *
 * Copyright 2013-2017 Bob Ray <https://bobsguides.com>
 * Created on 06-07-2013
 *
 * @package example
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
/* @var xPDOObject[] $menus */

$action = $modx->newObject('modAction');
$action->fromArray( array (
  'id' => 1,
  'namespace' => 'example',
  'controller' => 'index',
  'haslayout' => true,
  'lang_topics' => 'example:default',
  'assets' => '',
), '', true, true);

$menus[1] = $modx->newObject('modMenu');
$menus[1]->fromArray( array (
  'text' => 'Example',
  'action' => 'home',
  'parent' => 'components',
  'description' => "string21~~Hello 'columbus'",
  'icon' => '',
  'menuindex' => 0,
  'params' => '',
  'handler' => '',
  'permissions' => '',
  'namespace' => 'unittest',
), '', true, true);

$menus[2] = $modx->newObject('modMenu');
$menus[2]->fromArray( array (
  'text' => 'Example',
  'action' => 'home',
  'parent' => 'components',
  'description' => 'string22~~Hello "columbus"',
  'icon' => '',
  'menuindex' => 0,
  'params' => '',
  'handler' => '',
  'permissions' => '',
  'namespace' => 'unittest',
), '', true, true);


$menus[3] = $modx->newObject('modMenu');
$menus[3]->fromArray( array (
  'text' => 'Example',
  'action' => 'home',
  'parent' => 'components',
  'description' => 'string23',
  'icon' => '',
  'menuindex' => 0,
  'params' => '',
  'handler' => '',
  'permissions' => '',
  'namespace' => 'unittest',
), '', true, true);

return $menus;
