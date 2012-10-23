<?php
/**
* Adds modActions and modMenus into package
*
* @package mycomponent
* @subpackage build
*/
/* @var $modx modX */

$action= $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'mycomponent',
    'controller' => 'index',
    'haslayout' => true,
    'lang_topics' => 'mycomponent.:default,lexicon',
    'assets' => '',
),'',true,true);

/* load action into menu */
$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'mycomponent',
    'parent' => 'components',
    'description' => 'mycomponent.menu_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
),'',true,true);
$menu->addOne($action);

return $menu;

/* As of MODX 2.3, all of the above will be replaced by something like this: */
/* Note: will route to the first found of the following:
 [namespace-path]controllers/[manager-theme]/index.class.php
 [namespace-path]controllers/default/index.class.php
 [namespace-path]controllers/index.class.php
*/

/*
$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'mycomponent',
    'parent' => 'components',
    'description' => 'mycomponent.menu_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
    'action' => 'index',
    'namespace' => 'mycomponent',
),'',true,true);
return $menu;
*/