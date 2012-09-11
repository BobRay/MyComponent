<?php
/**
 * addProject Snippet file for MyComponent extra
 *
 * @package mycomponent
 */

/**
 * Description
 * -----------
 * addProject will add a new Project to the MODx environment. Unlike Bootstrap, it is
 * not based upon a single set config file. It is rather an initial blank setup for the
 * MODx environment. It will allow the developer to specify the Namespace and the 
 * initial single Category. The rest will be handled in the MODx manager by placing 
 * new components, entries, etc into the Namespace or Categories.
 *
 * A Plugin for "MyComponent" will be made, such that if a file is added to the Category,
 * or Namespace, the User will be asked if they would like to add it to the Project. There
 * must be a distinction between a Project and a Package. The Project is the work in
 * progress. The Package is a distributable iteration of completed work. 
 *
 * Until the Class infrastructure is complete, this will remain as a placeholder using 
 * the code for Bootstrap.
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package mycomponent
 **/


$props =& $scriptProperties;

/* @var $modx modX */
$sourceRoot = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) .'/';


if (!defined('MODX_CORE_PATH')) {

    $configPath = $sourceRoot . '_build/build.config.php';
    require_once $configPath;
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx= new modX();
    $modx->initialize('mgr');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');
}
if (!php_sapi_name() == 'cli') {
    echo "<pre>\n"; /* used for nice formatting for log messages  */
}

$scriptProperties = array();

$props =& $scriptProperties;
require_once $sourceRoot . 'core/components/mycomponent/model/mycomponent/bootstrap.class.php';
$bootStrap = new Bootstrap($modx,$props);

$bootStrap->init($sourceRoot . '_build/build.config.php');
/* These can be run independently -- comment out the ones you don't want to run.
 * There's no risk in running all of them since no existing files or objects will
 * be overwritten. */
$bootStrap->createBasics();
$bootStrap->createAssetsDirs();
$bootStrap->createCategory();
$bootStrap->createElements();
$bootStrap->createResources();
$bootStrap->createPropertySets();
$bootStrap->createClassFiles();
$bootStrap->createValidators();
$bootStrap->createExtraResolvers();
$bootStrap->createInstallOptions();
$bootStrap->createNewSystemSettings();

/* These should be run only if their appropriate objects exist */
$bootStrap->connectPropertySetsToElements();
$bootStrap->connectSystemEventsToPlugins();
$bootStrap->connectTvsToTemplates();
$bootStrap->connectResourcesToTemplates();



$modx->log(MODX::LOG_LEVEL_INFO,'Finished!');

