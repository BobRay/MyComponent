<?php
/**
* Adds modActions and modMenus into package
*
* @package mycomponent
* @subpackage build
*/
$action= $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'mycomponent',
    'parent' => 0,
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