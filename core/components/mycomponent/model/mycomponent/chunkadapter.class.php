<?php


class ChunkAdapter extends ElementAdapter
{//This will never change.
    protected $dbClass = 'modChunk';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/chunk/create';
    protected $updateProcessor = 'element/chunk/update';
    protected string $modx3CreateProcessor =
        'MODX\Revolution\Processors\Element\Chunk\Create';
    protected string $modx3UpdateProcessor =
        'MODX\Revolution\Processors\Element\Chunk\Update';
    
// Database fields for the XPDO Object
    protected $myFields;


    final function __construct(&$modx, &$helpers, $fields, $mode=MODE_BOOTSTRAP, $object = null) {
        $this->name = $fields['name'];
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
        parent::__construct($modx, $helpers, $fields, $mode, $object);

    }

}