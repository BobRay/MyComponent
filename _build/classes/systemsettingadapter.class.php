<?php
// Include the Base Class (only once)
require_once('modxobjectadapter.class.php');

class SystemSettingAdapter extends MODxObjectAdapter
{//These will never change.
    final static protected $xPDOClass = 'modSystemSetting';
    static protected $xPDOClassIDKey = 'key';
    static protected $xPDOClassNameKey = 'key';
    static protected $xPDOClassParentKey = 'namespace';
    final static protected $xPDOTransportAttributes = array
    (   xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );
    
// Database Columns for the XPDO Object
    protected $myColumns;

    final public function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myColumns = $columns;
    }

/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */


/* *****************************************************************************
   Import Objects and Support Functions (in MODxObjectAdapter) 
***************************************************************************** */

    public function addToMODx($overwrite = false)
    {//Prepare Setting
        $this->myColumns['area'] = $this->myColumns[static::xPDOClassParentKey];
        $this->myColumns['editedon'] = time();
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
            $myComponent->sendLog(modX::LOG_LEVEL_INFO, 'Packaged Setting: '. $this->myColumns['key']);
            return true;
        }
    }
}