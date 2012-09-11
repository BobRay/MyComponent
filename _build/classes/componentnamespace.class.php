<?php

/**
 * Description
 * -----------
 * Abstracts the Namespace from the MODx Revolution installation. Every Component
 * has at least a single ComponentNamespace. Namespaces control Lexicons and System 
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
class ComponentNamespace {
/* @var $myComponent Component - The Component that this ComponentNamespace belongs to. */
    $myComponent;
/* @var $systemSettings array - An array of ComponentSystemSettings. */
    $systemSettings = array();

    __construct(&$component, $name)
    {
        $this->myComponent =& $component;
        $this->key = $name;
    }

    public function getFromDatabase()
    {//Get MODx from the Component Object
        $modx = $this->myComponent->modx;
    // Get the Namespace
        $fromDB = $modx->getObject('modNamespace', array('' => $this->myName));
        if ($fromDB)
        {//Set the class properties

        }
    }

    public function putToDatabase
}