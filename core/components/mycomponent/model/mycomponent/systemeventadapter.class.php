<?php
// Include the Base Class (only once)
require_once('modxobjectadapter.class.php');

class SystemEventAdapter extends MODxObjectAdapter
{//This will never change.
    final static protected $xPDOClass = 'modEvent';
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