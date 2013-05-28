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

// comment out the next line to make processor functional
class mc_ElementChangeCategoryProcessor extends modProcessor {
    public $classKey = 'modmc_Element';
    public $languageTopics = array('[[+packageNameLower]]:default');
    
    public function process() {

/* !!! Remove this line to make processor functional */
return $this->modx->error->success();

        if (!$this->modx->hasPermission('save_mc_element')) {
            return $this->modx->error->failure($this->modx->lexicon('access_denied'));
        }

        if (empty($scriptProperties['mc_elements'])) {
            return $this->failure($this->modx->lexicon('mc_packageName.mc_elements_err_ns'));
        }
        /* get parent */
        if (!empty($scriptProperties['category'])) {
            $category = $this->modx->getObject('modCategory',$scriptProperties['category']);
            if (empty($category)) {
                return $this->failure($this->modx->lexicon('mc_element.category_err_nf'));
            }
        }

        /* iterate over mc_elements */
        /** @var $mc_element modElement */
        $mc_elementIds = explode(',',$scriptProperties['mc_elements']);
        foreach ($mc_elementIds as $mc_elementId) {
            $mc_element = $this->modx->getObject($this->classKey,$mc_elementId);
            if ($mc_element == null) continue;
        
            $mc_element->set('category',$scriptProperties['category']);
            $mc_element->save(3600);
        }
        return $this->success();
    }



}

return 'mc_ElementChangeCategoryProcessor';