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
    $processorsPath = MODX_CORE_PATH . '/processors/';
    if (!class_exists('modProcessor')) {
        $includeFile = MODX_CORE_PATH . 'model/modx/modprocessor.class.php';
        require $includeFile;
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

        /* Remove this line to make processor functional */
        return true;

        $parentClass = get_parent_class($this);

        if ($parentClass) {
            $reflectionMethod = new ReflectionMethod($parentClass, 'process');
            if (!$reflectionMethod->isAbstract()) {
                return parent::initialize();
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Cannot call abstract parent method');
                return $this->failure();
            }
        }

    }

    /**
     * Convert category ID to category name for objects with a category.
     * Convert template ID to template name for objects with a template
     *
     * Note: It's much more efficient to do these with a join, but that can
     * only be done for objects known to have the field. This code can
     * be used on any object.
     *
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $fields = $object->toArray();
        if (array_key_exists('category', $fields)) {
            if (!empty($fields['category'])) {
                $categoryObj = $this->modx->getObject('modCategory', $fields['category']);
                if ($categoryObj) {
                    $fields['category'] = $categoryObj->get('category');
                } else {
                    $fields['category'] = $this->modx->lexicon('invalid_category');
                }
            } else {
                $fields['category'] = $this->modx->lexicon('none');
            }
        }
        if (array_key_exists('template', $fields)) {
            if (!empty($fields['template'])) {
                $templateObj = $this->modx->getObject('modTemplate', $fields['template']);
                if ($templateObj) {
                    $fields['template'] = $templateObj->get('category');
                } else {
                    $fields['template'] = $this->modx->lexicon('invalid_template');
                }
            } else {
                $fields['template'] = $this->modx->lexicon('none');
            }
        }


        return $fields;
    }

    public function process() {

        /* perform action here */

        /* Remove this line to make processor functional */
        return $this->success();

        /* Make sure we're not calling an abstract method */
        $parentClass = get_parent_class($this);

        if ($parentClass) {
            $reflectionMethod = new ReflectionMethod($parentClass, 'process');
            if (!$reflectionMethod->isAbstract()) {
                return parent::process();
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Cannot call abstract parent method');
            }
        }

    }
}

return 'mc_processor_name';
