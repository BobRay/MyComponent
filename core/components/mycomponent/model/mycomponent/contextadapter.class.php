<?php

class ContextAdapter extends ObjectAdapter
{//This will never change.
    static protected $xPDOClass = 'modContext';
    static protected $xPDOTransportAttributes = array
    (   xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );

// Database Columns for the XPDO Object
    protected $myColumns;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields))
            $this->myFields = $fields;
    }
}
