<?php
/**
 * Resource objects for the MyComponent package
 * @author Your Name <you@yourdomain.com>
 * 1/1/11
 *
 * @package mycomponent
 * @subpackage build
 */

$resources = array();

$modx->log(modX::LOG_LEVEL_INFO,'Packaging resource: resource1<br />');
$resources[1]= $modx->newObject('modResource');
$resources[1]->fromArray(array(
    'id' => 1,
    'class_key' => 'modResource',
    'context_key' => 'web',
    'type' => 'document',
    'contentType' => 'text/html',
    'pagetitle' => 'Resource1',
    'longtitle' => 'Resource One',
    'description' => 'Resource1 description',
    'alias' => 'resource1',
    'published' => '1',
    'parent' => '0',
    'isfolder' => '0',
    'richtext' => '0',
    'menuindex' => '',
    'searchable' => '0',
    'cacheable' => '1',
    'menutitle' => 'Resource1',
    'donthit' => '0',
    'hidemenu' => '0',
),'',true,true);
$resources[1]->setContent(file_get_contents($sources['build'] . 'data/resources/resource1.content.html'));

$modx->log(modX::LOG_LEVEL_INFO,'Packaging resource: resource2<br />');
$resources[2]= $modx->newObject('modResource');
$resources[2]->fromArray(array(
    'id' => 2,
    'class_key' => 'modResource',
    'context_key' => 'web',
    'type' => 'document',
    'contentType' => 'text/html',
    'pagetitle' => 'Resource2',
    'longtitle' => 'Resource Two',
    'description' => 'Resource2 description',
    'alias' => 'resource2',
    'published' => '1',
    'parent' => '0',
    'isfolder' => '0',
    'richtext' => '0',
    'menuindex' => '',
    'searchable' => '0',
    'cacheable' => '1',
    'menutitle' => 'Resource2',
    'donthit' => '0',
    'hidemenu' => '1',
),'',true,true);
$resources[2]->setContent(file_get_contents($sources['build'] . 'data/resources/resource2.content.html'));

return $resources;
