<?php
/**
 * Array of plugin events for Mycomponent package
 *
 * @package mycomponent
 * @subpackage build
 */
$events = array();

$events['OnBeforeManagerLogin']= $modx->newObject('modPluginEvent');
$events['OnBeforeManagerLogin']->fromArray(array(
    'event' => 'OnBeforeManagerLogin',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

$events['OnManagerLoginFormRender']= $modx->newObject('modPluginEvent');
$events['OnManagerLoginFormRender']->fromArray(array(
    'event' => 'OnManagerLoginFormRender',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;