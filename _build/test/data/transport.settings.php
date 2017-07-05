<?php
/**
 * SystemSettings transport file for Example extra
 *
 * Copyright 2012-2017 Bob Ray <https://bobsguides.com>
 * Created on 08-25-2012
 *
 * @package example
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */

$systemsettings = array();

$systemsettings[1] = $modx->newObject('modSystemSetting');
$systemsettings[1] ->fromArray(array(
    'id' => 1,
    'key' => "setting_one",
    'name' => 'Setting One',
    'description' => "Hello 'columbus'",
    'value' => 'Test System Setting Value',
    'xtype' => 'textfield',
    'namespace' => 'example',
    'area' => 'example',
    'properties' => '',
), '', true, true);

$systemsettings[2] = $modx->newObject('modSystemSetting');
$systemsettings[2] ->fromArray(array(
    'id' => 2,
    'key' => 'setting_two',
    'name' => 'Setting Two',
    'description' => 'Hello "columbus"',
    'value' => 'Test System Setting Value',
    'xtype' => 'textfield',
    'namespace' => 'example',
    'area' => 'example',
    'properties' => '',
), '', true, true);

$systemsettings[3] = $modx->newObject('modSystemSetting');
$systemsettings[3] ->fromArray(array(
    'id' => 3,
    'key' => 'setting_three',
    'name' => 'Setting Three',
    'description' => 'Hello Columbus',
    'value' => 'Test System Setting Value',
    'xtype' => 'textfield',
    'namespace' => 'example',
    'area' => 'example',
    'properties' => '',
), '', true, true);

return $systemsettings;
