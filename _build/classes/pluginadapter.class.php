<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class PluginAdapter extends ElementAdapter
{
    final static protected $xPDOClass = 'modPlugin';
    
// Database Columns for the XPDO Object
    protected $myColumns;

    final public function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myColumns = $columns;
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