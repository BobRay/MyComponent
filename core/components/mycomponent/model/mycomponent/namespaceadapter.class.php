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


        parent::__construct($modx, $helpers);
    }

   /* public function getName() {
        return $this->myFields['name'];
    }

    public function getProcessor($mode) {
        return $mode == 'create'
            ? $this->createProcessor
            : $this->updateProcessor;
    }*/

    public function addToMODx($overwrite = false) {

        parent::addToMODx($overwrite);
    }

    public function getFromDatabase()
    {//Get MODx from the Component Object
        $modx = $this->modx;
    // Get the Namespace
        $fromDB = $modx->getObject('modNamespace', array('' => $this->myName));
        if ($fromDB)
        {//Set the class properties

        }
    }


    public function build(&$useBuilder)
    {
        $builder =& $useBuilder;
        if (!$this->myKey)
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Namespace has no Properties');
            return false;
        }
        
    // Package the Namespace
        $success = $builder->registerNamespace
        (   $this->myKey,
            false,
            true,
            '{core_path}components/'.$this->myComponent->getFriendlyName().'/'
        );
    // Report Failure and Return
        if (!$success)
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Could not package Namespace: '.$this->myKey);
            return $success;
        }

        $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Namespace: '.$this->myKey);
        $settings = $this->mySettings;
        if (!empty($settings)
        &&  is_array($settings))
        {   foreach($settings as $setting)
                $setting->build($builder);
        }
        
    // Return Success or Failure
        return $success;
    }
    
    public function loadChildren($data)
    {//Load System Settings
        $settings = include $data.'transport.settings.php';
        foreach ($settings as $setting)
            $this->mySettings[] = new ComponentSetting($this, $setting);
    }
}