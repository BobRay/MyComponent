<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 8/17/12
 * Time: 4:17 AM
 * To change this template use File | Settings | File Templates.
 */
class UtHelpers
{

    function __construct()
    {

    }

    /** recursive remove dir function */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }

    }

    /**
     * @param $modx modX
     * @param $bootstrap Bootstrap
     * Remove all elements specified in project config */
    public function removeElements(&$modx, &$bootstrap) {
        $props = $bootstrap->props;
        $elements = $props['elements'];
        foreach($elements as $elementType => $objectList) {
            $elementType = 'mod' . ucFirst(substr($elementType, 0, -1));
            foreach ($objectList as $elementName => $fields) {
                /* @var $obj modElement */
                $alias = $bootstrap->helpers->getNameAlias($elementType);
                $obj = $modx->getObject($elementType, array($alias => $elementName) );
                if ($obj) $obj->remove();

            }
        }
   }

    /**
     * @param $modx modX
     * @param $bootstrap Bootstrap
     * Remove all resources specified in project config */
   public function removeResources(&$modx, &$bootstrap) {
       /* @var $r modResource */
       $resources = $bootstrap->props['resources'];

       foreach ($resources as $resource => $fields) {
           $r = $modx->getObject('modResource', array('pagetitle' => $resource));
           if ($r) {
               $r->remove();
           }
       }
   }

    /**
     * @param $modx modX
     * @param $bootstrap Bootstrap
     */
    public function removePropertySets(&$modx, &$bootstrap) {
       /* @var $setObj modPropertySet */
       $sets = $bootstrap->props['elements']['propertySets'];

       foreach ($sets as $set) {
           $alias = $bootstrap->helpers->getNameAlias('modPropertySet');
           $setObj = $modx->getObject('modPropertySet', array($alias => $set));
           if ($setObj) {
               $setObj->remove();
           }
       }

   }

    /**
     * @param $elementType string - 'modChunk', 'modSnippet', etc.
     * @return string - The name of the 'name' field for the object (name, pagetitle, etc.)
     */
    public function getNameAlias($elementType)
    {
        switch ($elementType) {
            case 'modTemplate':
                $nameAlias = 'templatename';
                break;
            case 'modCategory':
                $nameAlias = 'category';
                break;
            case 'modResource':
                $nameAlias = 'pagetitle';
                break;
            default:
                $nameAlias = 'name';
                break;
        }
        return $nameAlias;

    }
    /** Add properties to elements for testing */
    public function createProperties(&$modx, &$bootstrap) {
        /* @var $modx modX */
        $properties = array(
            'property1' => 'value1',
            'property2' => 'value2',
            'property3' => 'value3',
            'property4' => 'value4',
        );
        $props = $bootstrap->props;
        $elements = $props['elements'];
        foreach ($elements as $elementType => $objectList) {
            foreach ($objectList as $elementName) {
                /* @var $obj modElement */
                $alias = $this->getNameAlias($elementType);
                $obj = $modx->getObject($elementType, array($alias => $elementName));
                if ($obj) $obj->setProperties($properties);
                $obj->save();

            }
        }

    }

    /** Add properties to elements for testing */
    public function createPropertysetProperties(&$modx, &$bootstrap)
    {
        /* @var $modx modX */
        $properties = array(
            'property1' => 'value1',
            'property2' => 'value2',
            'property3' => 'value3',
            'property4' => 'value4',
        );
        $props = $bootstrap->props;
        $elements = $props['elements']['propertySets'];

        $elementType = 'modPropertySet';
        foreach ($elements as $elementName) {
                /* @var $obj modElement */
                $alias = $this->getNameAlias($elementType);
                $obj = $modx->getObject($elementType, array($alias => $elementName));
                if ($obj) $obj->setProperties($properties);
                $obj->save();
        }

    }

    public function removeSystemSettings(&$modx, &$bootstrap) {
        /* @var $modx modX */
        /* @var $setting modSystemSetting */
        $settings = $modx->getCollection('modSystemSetting', array('namespace' => $bootstrap->props['category']));
        foreach($settings as $setting) {
            $setting->remove();
        }


    }
}

