<?php
/**
 * Processor file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
 * [[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage processors
 */

/* call with:
    $processorsPath = MODX_CORE_PATH . 'yourcomponent/model/processors/';
    $options = array('processors_path' => $processorsPath);
    $action = 'mgr/snippet/getlist'; // per your config file with : replaced by /
    $result = $modx->runSnippet($action, $scriptProperties, $options);

    return value is generally a JSON string like this:
       {"success":true,"total":3,"results":[{etc.},{etc.},{etc.}]}
*/

/* @var $modx modX */
if (!defined('MODX_CORE_PATH')) {
    include dirname(__FILE__, 5) . '/config.core.php';
    if (empty(MODX_CORE_PATH)) {
        /* For dev. environment */
        include dirname(__FILE__, 8) . '/config.core.php';
    }
}

if (empty(MODX_CORE_PATH)) {
    die('Could not find config.core.php');
}

$v = include MODX_CORE_PATH . 'docs/version.inc.php';
$isMODX3 = $v['version'] >= 3;

/* Note: mc_processor_parent is *not* the processor's parent.
   It's a local processor name used only once to extend
   this processor */
if ($isMODX3) {
    abstract class mc_processor_parent extends mc_modx3_extends {
    }

} else {
    $includeFile = 'mc_modx2_include';
    if (file_exists($includeFile)) {
        include $includeFile;
    } else {
        return "Include File does not exist";
    }
    abstract class mc_processor_parent extends mc_modx2_extends {

    }
}

class mc_processor_name extends mc_processor_parent {
    public $languageTopics = array('mc_package_name_lower:default');
    public $defaultSortField = 'mc_name_field';
    public $defaultSortDirection = 'ASC';

    function initialize() {
        /* Initialization here */
        return parent::initialize();
    }

    public function process() {

        /* perform action here */
        return $this->success('Hello');

        /* Use this only if parent is not an abstract class */
       // return parent::process();

    }
}

return 'mc_processor_name';
