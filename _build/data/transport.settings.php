<?php
/** Array of system settings for Mycomponent package
 * @package mycomponent
 * @subpackage build
 */


/* This section is ONLY for new System Settings to be added to
 * The System Settings grid. If you include existing settings,
 * they will be removed on uninstall. Existing setting can be
 * set in a script resolver (see install.script.php).
 */
$settings = array();

/* The first three are new settings */
$settings['mycomponent_setting1']= $modx->newObject('modSystemSetting');
$settings['mycomponent_setting1']->fromArray(array (
    'key' => 'mycomponent_setting1',
    'value' => 'Value for setting 1',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent_setting2']= $modx->newObject('modSystemSetting');
$settings['mycomponent_setting2']->fromArray(array (
    'key' => 'mycomponent_setting2',
    'value' => '1',
    'xtype' => 'combo-boolean',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent_setting3']= $modx->newObject('modSystemSetting');
$settings['mycomponent_setting3']->fromArray(array (
    'key' => 'mycomponent_setting3',
    'value' => '0',
    'xtype' => 'combo-boolean',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

return $settings;