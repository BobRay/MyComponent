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
/* This is for Lexicon Helper
 * $modx->lexicon->load('mycomponent:default');
 */
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
$prefix = $isMODX3? 'MODX\Revolution\\' : '';

if ($isMODX3) {
    abstract class mc_processor_parent extends MODX\Revolution\Processors\Element\mc_Element\GetList {
    }
} else {

        $includeFile = MODX_CORE_PATH . 'model/modx/processors/element/mc_element/getlist.class.php';
    if (!class_exists($prefix . 'modmc_ElementGetListProcessor')) {
        require $includeFile;
    }

    abstract class mc_processor_parent extends modmc_ElementGetlistProcessor {
    }
}
class mc_processor_name extends mc_processor_parent {
    public $classKey = 'modmc_Element';
    public $languageTopics = array('mc_packageNameLower:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';

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
                    $fields['category'] = $this->modx->lexicon('invalid_category~~Invalid Category');
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
                    $fields['template'] = $this->modx->lexicon('invalid_template~~Invalid Template');
                }
            } else {
                $fields['template'] = $this->modx->lexicon('none');
            }
        }

        return $fields;
    }

    /* Use this if you want to perform custom actions
       and ignore the parent's process() method. The Parent's
       process will be called automatically if this is left
       commented out
    */

     public function process() {
        /* Perform action here */

         return parent::process();
        // return $this->success();
    }
}
return 'mc_processor_name';
