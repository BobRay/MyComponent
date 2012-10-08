<?php


class TemplateVarAdapter extends ElementAdapter
{
    protected $dbClass = 'modTemplateVar';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/tv/create';
    protected $updateProcessor = 'element/tv/update';
    

    protected $fields;
    protected $name;

    final public function __construct(&$modx, &$helpers, $fields) {
        $this->name = $fields['name'];
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
        parent::__construct($modx, $helpers);

    }

    public function addToMODx($overwrite = false) {
        parent::addToModx($overwrite);
        $fields = $this->myFields;
        $defaultTemplateId = $this->modx->getOption('default_template');
        if (isset($fields['templates'])) {
            foreach($fields['templates'] as $templateName => $rank) {
                if ($templateName == 'default') {
                    $templateId = $defaultTemplateId;
                } else {
                    $templateObj = $this->modx->getObject('modTemplate', array('templatename' => $templateName));
                    $templateId = $templateObj? $templateObj->get('id') : $defaultTemplateId;
                }
                if ($templateId && $this->myId) {
                    $tvtFields = array();
                    $tvtFields['tmplvarid'] = $this->myId;
                    $tvtFields['templateid'] = $templateId;
                    $tvt = $this->modx->getObject('modTemplateVarTemplate', $tvtFields);

                    $tvtFields['rank'] = $rank;
                    if (! $tvt) {
                        $tvt = $this->modx->newObject('modTemplateVarTemplate');
                        foreach($tvtFields as $k => $v) {
                            $tvt->set($k, $v);
                        }
                        if ($tvt->save()) {
                            $msg =  '    Connected ' . $this->getName() . ' TV to ' . $templateName . ' template';
                            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, $msg);
                        } else {
                            $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, "Error creating TemplateVarTemplate");
                        }
                    } else {
                        $msg = '    ' .  $this->getName() . ' TV is already connected to ' . $templateName . ' template';
                        $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, $msg);
                    }

                } else {
                    $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, "Error creating TemplateVarTemplate");
                }


            }
        }

    }
/* *****************************************************************************
   Bootstrap and Support Functions (in ElementAdapter)
***************************************************************************** */

/* *****************************************************************************
   Import Objects and Support Functions (in ElementAdapter) 
***************************************************************************** */

/* *****************************************************************************
   Export Objects and Support Functions (in ElementAdapter)
***************************************************************************** */

/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */
    final public function buildVehicle()
    {//Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->properties['pagetitle']);
            return true;
        }
    }
}