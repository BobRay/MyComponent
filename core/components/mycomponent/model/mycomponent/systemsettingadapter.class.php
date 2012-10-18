<?php
// Include the Base Class (only once)

class SystemSettingAdapter extends ObjectAdapter
{//These will never change.
    protected $dbClass = 'modSystemSetting';
    protected $dbClassIDKey = 'key';
    protected $dbClassNameKey = 'key';
    protected $dbClassParentKey = 'namespace';
    protected $createProcessor = 'system/settings/create';
    protected $updateProcessor = 'system/settings/update';


    /* Database Columns for the XPDO Object */
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields) {

        $this->modx =& $modx;
        $this->helpers =& $helpers;
        $this->myComponent =& $myComponent;

        if (! isset($fields['namespace'])) {
            $fields['namespace'] = $this->helpers->props['packageNameLower'];
        }
        if (! isset ($fields['name'])) {
            $fields['name'] = $fields['key'];
        }
        if (is_array($fields)) {
            $this->myFields =& $fields;
        }
        if (!isset($fields['area'])) {
            $fields['area'] = $this->myFields[$this->dbClassParentKey];
        }
        $this->name = $fields['key'];
        ObjectAdapter::$myObjects['newSystemSettings'][] = $fields;
        parent::__construct($modx, $helpers);
    }

/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */


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
            $myComponent->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Packaged Setting: '. $this->myColumns['key']);
            return true;
        }
    }
}