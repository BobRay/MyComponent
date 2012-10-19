<?php
// Include the Base Class (only once)

class SystemEventAdapter extends ObjectAdapter
{//This will never change.
    protected $dbClass = 'modEvent';
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;


// Database Columns for the XPDO Object
    protected $myFields;
    protected $name;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        $this->name = $fields['name'];

        if ($mode == MODE_BOOTSTRAP) {
            if (empty($fields['groupname'])) {
                $fields['groupname'] = $this->helpers->props['packageName'];
            }
            if (empty($fields['service'])) {
                $fields['service'] = 1;
            }
        }
        ObjectAdapter::$myObjects['newSystemEvents'][] = $fields;
        $this->myFields = $fields;
    }


    
/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */

    /* Move to ObjectAdapter as alternate method? */
    public function addToMODx($overwrite = false) {
        $name = $this->getName();
        $retVal = false;
        $obj = $this->modx->getObject('modEvent', array('name'=> $name));
        if (! $obj) {
            $event = $this->modx->newObject('modEvent');
            if ($event && $event instanceof modEvent) {
                $event->fromArray($this->myFields, "", true, true);
                if ($event->save()) {
                    $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    Created System Event: ' . $name);
                    $retVal = true;
                } else {
                    $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, '    Could not save System Event: ' . $name);
                }
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, '    Could not create System Event: ' . $name);
            }
        } elseif ($overwrite) {
            foreach($this->myFields as $field => $value) {
                $obj->set($field, $value);
            }
            if ($obj->save()) {
                $retVal = true;
                $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    Updated System Event: ' . $name);
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    Failed to updated System Event: ' . $name);
            }

        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    System Event already exists: ' . $name);
            $retVal = -1;
        }
        return $retVal;
    }
/* *****************************************************************************
   Import Objects and Support Functions (in MODxObjectAdapter) 
***************************************************************************** */

/* *****************************************************************************
   Export Objects and Support Functions (in MODxObjectAdapter)
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