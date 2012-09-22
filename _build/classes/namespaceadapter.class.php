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
class NamespaceAdapter 
{
/* @var $myComponent Component - The Component that this ComponentNamespace belongs to. */
    $myComponent;
    
/* @var $myKey string - The Key of the ComponentNamespace. */
   $myKey;
   
/* @var $systemSettings array - An array of ComponentSystemSettings. */
    $mySettings = array();

    __construct(&$component, $name)
    {
        $this->myComponent =& $component;
        $this->$myKey = $name;
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