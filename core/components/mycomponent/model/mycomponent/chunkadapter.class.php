<?php


class ChunkAdapter extends ElementAdapter
{//This will never change.
    protected $dbClass = 'modChunk';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/chunk/create';
    protected $updateProcessor = 'element/chunk/update';
    
// Database fields for the XPDO Object
    protected $myFields;
    protected $name;

    final function __construct(&$modx, &$helpers, $fields) {
        $this->name = $fields['name'];
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
        parent::__construct($modx, $helpers);

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
    final public function buildVehicle() {//Add to the Transport Package
        /* @var $myComponent MyComponentProject */
        if (parent::buildVehicle()) {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->properties['pagetitle']);
            return true;
        } else {
            return false;
        }
    }
}