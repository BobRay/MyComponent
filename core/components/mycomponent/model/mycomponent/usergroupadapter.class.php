<?php

class UserGroupAdapter extends ObjectAdapter {
    static protected $xPDOClass = 'modUserGroup';

// Database Columns for the XPDO Object
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields))
            $this->myFields = $fields;
    }

}