<?php

class ContextSettingAdapter extends ObjectAdapter
{//This will never change.
    static protected $xPDOClass = 'modContextSetting';

// Database Columns for the XPDO Object
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields))
            $this->myFields = $fields;
    }
}