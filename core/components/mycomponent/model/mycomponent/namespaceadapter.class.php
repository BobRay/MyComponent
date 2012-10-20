<?php

/**
 * Description
 * -----------
 * Abstracts the Namespace from the MODx Revolution installation. Every Component
 * has at least a single Namespace. Namespaces control Lexicons and System
 * Settings.
 *
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package mycomponent
 **/
class NamespaceAdapter extends ObjectAdapter {

    protected $dbClass = 'modNamespace';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = '';
    protected $createProcessor = 'workspace/namespace/create';
    protected $updateProcessor = 'workspace/namespace/update';
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;


    function __construct(&$modx, &$helpers, $fields, $createChildren = false) {
        /* @var $helpers Helpers */
        $this->myComponent =& $component;
        $this->modx =&$modx;

        $this->helpers =& $helpers;
        $this->myFields = $fields;
        $this->name = $fields['name'];

        ObjectAdapter::$myObjects['nameSpaces'][] = $fields;
        parent::__construct($modx, $helpers);
    }

}