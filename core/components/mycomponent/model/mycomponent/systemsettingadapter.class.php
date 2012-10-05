<?php
// Include the Base Class (only once)

class SystemSettingAdapter extends ObjectAdapter
{//These will never change.
    protected $dbClass = 'modSystemSetting';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'key';
    protected $dbClassParentKey = 'namespace';

    static protected $xPDOClassParentKey = 'namespace';
    /*final static protected $xPDOTransportAttributes = array
    (   xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );*/
    
// Database Columns for the XPDO Object
    protected $myFields;

    final public function __construct(&$myComponent, $fields)
    {   parent::__construct(&$fields);
        $this->myComponent =& $myComponent;
        if (is_array($fields))
            $this->myFields =& $fields;
    }

/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */


/* *****************************************************************************
   Import Objects and Support Functions (in MODxObjectAdapter) 
***************************************************************************** */

    public function addToMODx($overwrite = false)
    {//Prepare Setting
        $this->myFields['area'] = $this->myColumns[static::$xPDOClassParentKey];
        $this->myFields['editedon'] = time();
    // Default Functionality
        parent::addToMODx($overwrite);
    /* QUESTION: Might want to automatically add Lexicon Entries? */
    }
    
    /** Deprecated: see $this->addToMODx(); called from NamespaceAdapter->addToMODx() */
    private function createNewSystemSettings()     {    }
 
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