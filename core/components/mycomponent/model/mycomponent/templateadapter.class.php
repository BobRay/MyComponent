<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class TemplateAdapter extends ElementAdapter
{
    protected $dbClass = 'modTemplate';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'templatename';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/template/create';
    protected $updateProcessor = 'element/template/update';
    
// Database fields for the XPDO Object
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        if (isset($fields['name'])) {
            $fields['templatename'] = $fields['name'];
            unset($fields['name']);
        }
        $this->name = $fields['templatename'];
        if (is_array($fields)) {
            $this->myFields = $fields;
        }

        parent::__construct($modx, $helpers, $fields, $mode);

    }
}