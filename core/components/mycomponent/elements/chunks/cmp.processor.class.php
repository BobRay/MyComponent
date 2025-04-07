<?php
/**
 * Processor file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage processors
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

if ($isMODX3) {
    abstract class DynamicBaseProcessorParent extends MODX\Revolution\Processors\Processor {
    }
} else {
    if (!class_exists('modProcessor')) {
        include MODX_CORE_PATH . 'model\modx\modprocessor.class.php';
    }

    abstract class DynamicBaseProcessorParent extends modProcessor {
    }
}

class mc_ProcessorTypeProcessor extends DynamicBaseProcessorParent {
    public $classKey = 'modmc_Element';
    public $languageTopics = array('mc_packageNameLower:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $ids;

    function initialize() {
        /* Initialization here */
        return true;
    }

    /* For built-in processors (create, update, duplicate, remove),
       this method can be removed */
    public function process() {

        /* perform action here */

        return $this->success();

    }
}

return 'mc_ProcessorTypeProcessor';
