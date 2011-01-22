<?php
/**
 * Array of plugin events for Mycomponent package
 *
 * @package mycomponent
 * @subpackage build
 */
$events = array();

$events['OnBeforeUserFormSave']= $modx->newObject('modPluginEvent');
$events['OnBeforeUserFormSave']->fromArray(array(
    'event' => 'OnBeforeUserFormSave',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

$events['OnUserFormSave']= $modx->newObject('modPluginEvent');
$events['OnUserFormSave']->fromArray(array(
    'event' => 'OnUserFormSave',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;