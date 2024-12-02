<?php
chdir('C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\_build');


/* @var $modx modX
 * @var $scriptProperties array
 *
 **/

use Codeception\Util\Fixtures;

require_once 'c:/xampp/htdocs/addons/core/model/modx/modx.class.php';
echo "Getting MODX";
$modx = new modX();
$modx->getRequest();
$modx->getService('error', 'error.modError', '', '');
$modx->initialize('mgr');
Fixtures::add('modx', $modx);

include('C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\core\components\mycomponent\model\mycomponent\helpers.class.php');

$props = include 'C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\_build\config\unittest.config.php';
$helpers = new Helpers($modx, $props);
Fixtures::add('helpers', $helpers);

include('C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\core\components\mycomponent\model\mycomponent\mycomponentproject.class.php');

$myComponentProject = new MyComponentProject($modx);
// $myComponentProject->init($props, 'unittest');
Fixtures::add('myComponentProject', $myComponentProject);

$file = 'C:\xampp\htdocs\addons\assets\mycomponents\mycomponent\core\components\mycomponent\model\mycomponent\mcautoload.php';

require $file;
spl_autoload_register('mc_auto_load');

$x = 1;
