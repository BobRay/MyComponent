<?php
/** Array of system settings for Mycomponent package
 * @package mycomponent
 * @subpackage build
 */
$settings = array();

$settings['mycomponent.words']= $modx->newObject('modSystemSetting');
$settings['mycomponent.words']->fromArray(array (
    'key' => 'mycomponent.words',
    'value' => 'Ack,Arps,Alag,Atex,Bek,Bux,Chux,Caxt,Depp,Dex,Ext,Enya,Fet,Fets,Tek,Text,Gurk,Gex,Het,Heft,Unet,Ibex,Jax,Jerp,Jenk,Lak,Lest,Lev,Mars,Mamp,Nex,Nelp,Paxt,Pex,Reks,Rux,Snix,Sept,Turp,Thix,Elps,Vux,Veks,Wect,Wex,Yap,Yef,Yeff,Zub,Zeks',
    'xtype' => 'textarea',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent.enabled']= $modx->newObject('modSystemSetting');
$settings['mycomponent.enabled']->fromArray(array (
    'key' => 'mycomponent.enabled',
    'value' => '0',
    'xtype' => 'combo-boolean',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent.use_mathstring']= $modx->newObject('modSystemSetting');
$settings['mycomponent.use_mathstring']->fromArray(array (
    'key' => 'mycomponent.use_mathstring',
    'value' => '1',
    'xtype' => 'combo-boolean',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent.height']= $modx->newObject('modSystemSetting');
$settings['mycomponent.height']->fromArray(array (
    'key' => 'mycomponent.height',
    'value' => '80',
    'xtype' => 'textfield',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

$settings['mycomponent.width']= $modx->newObject('modSystemSetting');
$settings['mycomponent.width']->fromArray(array (
    'key' => 'mycomponent.width',
    'value' => '200',
    'xtype' => 'textfield',
    'namespace' => 'mycomponent',
    'area' => 'mycomponent',
), '', true, true);

return $settings;