<?php
/**
 * Processor file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage processors
 */

/* @var $modx modX */


class mc_ProcessorTypeProcessor extends modObjectGetListProcessor {
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
}
return 'mc_ProcessorTypeProcessor';
