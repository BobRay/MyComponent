<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class TemplateAdapter extends ElementAdapter
{
    final static protected $dbClass = 'modTemplate';
    final static protected $dbClassIDKey = 'id';
    final static protected $dbClassNameKey = 'templatename';
    final static protected $dbClassParentKey = 'category';
    final static protected $dbTransportAttributes = array
    (   xPDOTransport::UNIQUE_KEY => 'category',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
        
    );
    
// Database Columns for the XPDO Object
    protected $myFields;

    final public function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myFields = $columns;
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