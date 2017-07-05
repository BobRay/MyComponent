<?php
/**
 * resources transport file for MyComponent extra
 *
 * Copyright 2012-2017 Bob Ray <https://bobsguides.com>
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
$resources[1]->fromArray(array(
    'id' => 1,
    'pagetitle' => 'MyComponent',
    'alias' => 'mycomponent',
    'longtitle' => 'MyComponent Control Center',
    'description' => 'MyComponent Control Center',
    'published' => '1',
    'hidemenu' => '1',
    'richtext' => '0',
    'template' => 'MyComponentTemplate',
    'class_key' => 'modDocument',
    'cacheable' => '1',
    'searchable' => '1',
    'properties' => '',
), '', true, true);
$resources[1]->setContent(file_get_contents($sources['data'].'resources/mycomponent.content.html'));

return $resources;
