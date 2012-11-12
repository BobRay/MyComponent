<?php


class UserGroupRoleAdapter extends ObjectAdapter {
    static protected $xPDOClass = 'modUserGroupRole';

// Database Columns for the XPDO Object
    protected $myColumns;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
    }
}