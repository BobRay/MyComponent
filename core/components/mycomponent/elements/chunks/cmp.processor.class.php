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


class mc_ProcessorTypeProcessor extends modProcessor {
    public $classKey = 'modmc_Element';
    public $languageTopics = array('mc_packageNameLower:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $ids;

    function initialize() {
        // $ids come in as a string, so this will work with a single id or
        // comma-separated multiple ids for batch processing.

        /*  $ids = $this->getProperty('ids', '');
        if (empty($ids)) {
             return $this->failure($this->modx->lexicon('ids_not_specified'));
        }

        if (! is_array($ids) {
            $ids = explode(',', $ids);
        }
        $this->ids = $ids;

        */

        return true;
    }

    /* For built-in processors (create, update, duplicate, remove),
       this method can be removed */
    public function process() {

        /*
        foreach ($this->ids as $id) {
             if (empty($id)) {
                 return $this->failure($this->modx->lexicon('id_is_empty'));
             }

             if (! is_numeric($id)) {
                 return $this->failure($this->modx->lexicon('id_not_a_number'));
             }

            // perform action here

        }*/

        return $this->success();


    }
}

return 'mc_ProcessorTypeProcessor';
