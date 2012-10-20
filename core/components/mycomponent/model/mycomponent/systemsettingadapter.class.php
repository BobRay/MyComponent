<?php
// Include the Base Class (only once)

class SystemSettingAdapter extends ObjectAdapter
{//These will never change.
    protected $dbClass = 'modSystemSetting';
    protected $dbClassIDKey = 'key';
    protected $dbClassNameKey = 'key';
    protected $dbClassParentKey = 'namespace';
    protected $createProcessor = 'system/settings/create';
    protected $updateProcessor = 'system/settings/update';


    /* Database Columns for the XPDO Object */
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {

        $this->modx =& $modx;
        $this->helpers =& $helpers;
        $this->myComponent =& $myComponent;

        if ($mode == MODE_BOOTSTRAP) {
            if (! isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->props['packageNameLower'];
            }
            if (! isset ($fields['name'])) {
                $fields['name'] = $fields['key'];
            }
            if (is_array($fields)) {
                $this->myFields =& $fields;
            }
            if (!isset($fields['area'])) {
                $fields['area'] = $this->myFields[$this->dbClassParentKey];
            }
        } elseif ($mode == MODE_EXPORT) {
            unset($fields['editedon']);
        }
        $this->name = $fields['key'];
        ObjectAdapter::$myObjects['newSystemSettings'][] = $fields;
        parent::__construct($modx, $helpers);
    }
}