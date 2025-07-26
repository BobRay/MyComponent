<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class SnippetAdapter extends ElementAdapter
{
    protected $dbClass = 'modSnippet';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/snippet/create';
    protected $updateProcessor = 'element/snippet/update';
    protected string $modx3CreateProcessor = 'MODX\Revolution\Processors\Element\Snippet\Create';
    protected string $modx3UpdateProcessor = 'MODX\Revolution\Processors\Element\Snippet\Update';

// Database Columns for the XPDO Object
    protected $myFields;


    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        $this->name = $fields['name'];
        parent::__construct($modx, $helpers, $fields, $mode);
    }
}
