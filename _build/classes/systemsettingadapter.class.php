<?php
// Include the Base Class (only once)
require_once('modxobjectadapter.class.php');

class SystemSettingAdapter extends MODxObjectAdapter
{//These will never change.
    final static protected $xPDOClass = 'modSystemSetting';
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
    
    final public function buildVehicle()
    {//Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Setting: '.$this->properties['key']);
            return true;
        }
    }
}