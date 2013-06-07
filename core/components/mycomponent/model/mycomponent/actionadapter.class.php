<?php

class ActionAdapter extends ObjectAdapter
{//This will never change.
    static protected $xPDOClass = 'modAction';

// Database Columns for the XPDO Object
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields))
            if (isset($fields['lang_topics']) && empty($fields['lang_topics'])) {
                $fields['lang_topics'] = $this->helpers->props['packageNameLower'] . ':default';
            }
            $this->myFields = $fields;
    }
}