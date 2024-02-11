<?php

namespace tests\unit;
use mc;
use modElement;
use modPropertySet;
use modResource;
use modSystemSetting;
use modX;

/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 8/17/12
 * Time: 4:17 AM
 * To change this template use File | Settings | File Templates.
 */
class UtHelpers {

    function __construct() {

    }

    /** recursive remove dir function */
    public function rrmdir($dir) {
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
     * @param $mc mc
     * Remove all elements specified in project config */
    public function removeElements(&$modx, &$mc) {
        $props = $mc->props;
        $elements = $props['elements'];
        foreach ($elements as $elementType => $objectList) {
            $elementType = 'mod' . ucFirst(substr($elementType, 0, -1));
            foreach ($objectList as $elementName => $fields) {
                /* @var $obj modElement */
                $alias = $mc->helpers->getNameAlias($elementType);
                $obj = $modx->getObject($elementType, array($alias => $elementName));
                if ($obj) {
                    $obj->remove();
                }

            }
        }
    }

    /**
     * @param $modx modX
     * @param $mc mc
     * Remove all resources specified in project config */
    public function removeResources(&$modx, &$mc) {
        /* @var $r modResource */
        $resources = $mc->props['resources'];

        foreach ($resources as $resource => $fields) {
            $r = $modx->getObject('modResource', array('pagetitle' => $resource));
            if ($r) {
                $r->remove();
            }
        }
    }

    /**
     * @param $modx modX
     * @param $mc mc
     */
    public function removePropertySets(&$modx, &$mc) {
        /* @var $setObj modPropertySet */
        $sets = $mc->props['elements']['propertySets'];

        foreach ($sets as $set) {
            $alias = $mc->helpers->getNameAlias('modPropertySet');
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
    public function getNameAlias($elementType) {
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
    public function createProperties(&$modx, &$mc) {
        /* @var $modx modX */
        $properties = array(
            'property1' => 'value1',
            'property2' => 'value2',
            'property3' => 'value3',
            'property4' => 'value4',
        );
        $props = $mc->props;
        $elements = $props['elements'];
        foreach ($elements as $elementType => $objectList) {
            $elementType = 'mod' . ucfirst(substr($elementType, 0, -1));
            foreach ($objectList as $name => $fields) {
                /* @var $obj modElement */

                $alias = $this->getNameAlias($elementType);

                $obj = $modx->getObject($elementType, array($alias => $name));
                if ($obj) {
                    $obj->setProperties($properties);
                    $obj->save();
                }

            }
        }

    }

    /** Add properties to elements for testing */
    public function createPropertysetProperties(&$modx, &$mc) {
        /* @var $modx modX */
        $properties = array(
            'property1' => 'value1',
            'property2' => 'value2',
            'property3' => 'value3',
            'property4' => 'value4',
        );
        $props = $mc->props;
        $elements = $props['elements']['propertySets'];

        $elementType = 'modPropertySet';
        foreach ($elements as $elementName => $fields) {
            /* @var $obj modElement */
            $alias = $this->getNameAlias($elementType);
            $obj = $modx->getObject($elementType, array($alias => $elementName));
            if ($obj) {
                $obj->setProperties($properties);
            }
            $obj->save();
        }

    }

    public function removeSystemSettings(&$modx, &$mc) {
        /* @var $modx modX */
        /* @var $setting modSystemSetting */
        foreach ($mc->props['namespaces'] as $namespace => $fields) {
            $settings = $modx->getCollection('modSystemSetting',
                array('namespace' => $namespace));
            foreach ($settings as $setting) {
                $setting->remove();
            }
        }
    }

    public function removeNamespaces(&$modx, &$mc) {
        /* @var $modx modX */
        foreach ($mc->props['namespaces'] as $namespace => $fields) {
            $ns = $modx->getObject('modNamespace', array('name' => $namespace));
            if ($ns) {
                $ns->remove();
            }
        }
    }

    public function removeCategories(&$modx, &$mc) {
        /* @var $modx modX */
        foreach ($mc->props['categories'] as $category => $fields) {
            $ct = $modx->getObject('modCategory', array('category' => $category));
            if ($ct) {
                $ct->remove();
            }
        }
    }
}
