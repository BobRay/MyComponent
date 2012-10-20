<?php

/* ToDo: make this work */

class MenuAdapter extends ObjectAdapter {
    static protected $xPDOClass = 'modMenu';


    protected $myFields;

    final public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
    }
}