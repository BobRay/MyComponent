<?php
/**
 * resources transport file for MyComponent extra
 *
 * Copyright 2012-2025 Bob Ray <https://bobsguides.com>
 * Created on 10-30-2012
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
/* @var xPDOObject[] $resources */


$resources = array();

$resources[1] = $modx->newObject('modResource');
$resources[1]->fromArray(array (
  'id' => 1,
  'type' => 'document',
  'contentType' => 'text/html',
  'pagetitle' => 'MyComponent',
  'longtitle' => 'MyComponent Control Center',
  'description' => 'MyComponent Control Center',
  'alias' => 'mycomponent',
  'alias_visible' => true,
  'link_attributes' => '',
  'published' => true,
  'isfolder' => false,
  'introtext' => '',
  'richtext' => false,
  'template' => 'MyComponentTemplate',
  'menuindex' => 1,
  'searchable' => true,
  'cacheable' => true,
  'createdby' => 0,
  'editedby' => 1,
  'deleted' => false,
  'deletedon' => 0,
  'deletedby' => 0,
  'menutitle' => '',
  'donthit' => false,
  'privateweb' => false,
  'privatemgr' => false,
  'content_dispo' => 0,
  'hidemenu' => false,
  'context_key' => 'web',
  'content_type' => 1,
  'hide_children_in_tree' => 0,
  'show_in_tree' => 1,
  'properties' => '',
), '', true, true);
$resources[1]->setContent(file_get_contents($sources['data'].'resources/mycomponent.content.html'));

return $resources;
