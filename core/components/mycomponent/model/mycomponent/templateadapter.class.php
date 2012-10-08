<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class TemplateAdapter extends ElementAdapter
{
    protected $dbClass = 'modTemplate';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'templatename';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/template/create';
    protected $updateProcessor = 'element/template/update';
    
// Database fields for the XPDO Object
    protected $myFields;
    protected $name;

    final public function __construct(&$modx, &$helpers, $fields) {
        $this->name = $fields['templatename'];
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
    final public function buildVehicle()
    {//Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->properties['pagetitle']);
            return true;
        }
    }
}