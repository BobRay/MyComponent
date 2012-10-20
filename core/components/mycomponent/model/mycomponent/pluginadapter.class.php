<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class PluginAdapter extends ElementAdapter
{
    protected $dbClass = 'modPlugin';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/plugin/create';
    protected $updateProcessor = 'element/plugin/update';
    
// Database Fields for the XPDO Object
    protected $myParent;
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $object modPlugin */
        /* @var $helpers Helpers */
        $this->helpers =& $helpers;
        $this->modx =& $modx;
        $this->name = $fields['name'];
        $this->setPluginResolver($fields, $mode);
        if (isset($fields['events'])) {
            unset($fields['events']);
        }
        parent::__construct($modx, $helpers, $fields, $mode);


    }
    public function setPluginResolver($fields, $mode) {
        $resolverFields[] = array();
        if ($mode == MODE_BOOTSTRAP) {
            /* bail out if no events in project config */
            if (! isset($fields['events']) || empty($fields['events'])) {
                $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    No events for plugin: ' . $this->getName());
                return;
            }
            $events = $fields['events'];
            foreach ($events as $eventName => $fields) {
                $resolverFields = array(
                    'pluginid' => $this->getName(),
                    'event' => isset($fields['event']) ? $fields['event'] : $eventName,
                    'priority' => isset($fields['priority']) && !empty($fields['priority'])? $fields['priority'] : '0',
                    'propertyset' => isset($fields['propertySet']) && !empty($fields['priority']) ? $fields['propertySet'] : '0',
                );
                ObjectAdapter::$myObjects['pluginResolver'][] = $resolverFields;
            }

        } elseif ($mode == MODE_EXPORT) {
            $me = $this->modx->getObject('modPlugin', array('name' => $this->getName()));
            if (!$me) {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, "Can't find myself");
            } else {
                $pes = $me->getMany('PluginEvents');
                if (! empty($pes)) {
                    foreach($pes as $pe) {
                        /* @var $pe modPluginEvent */
                        $fields = $pe->toArray();
                        $resolverFields = array(
                            'pluginid' => $this->getName(),
                            'event' => $fields['event'],
                            'priority' => isset($fields['priority']) && !empty($fields['priority'])
                                ? $fields['priority']
                                : '0',
                            'propertyset' => isset($fields['propertySet']) && !empty($fields['priority'])
                                ? $fields['propertySet']
                                : '0',
                        );
                        ObjectAdapter::$myObjects['pluginResolver'][] = $resolverFields;
                    }
                }
            }
        }

    }
    
/* *****************************************************************************
   Bootstrap and Support Functions (in ElementAdapter)
***************************************************************************** */

/* *****************************************************************************
   Import Objects and Support Functions (in ElementAdapter) 
***************************************************************************** */

    public function addToMODx($overwrite = false)
    {//Perform default export implementation
        parent::addToMODx($overwrite);
    }


    /**
     * Creates Resolver to for pluginEvents
     * @param $dir string - resolver directory
     * @param $intersects  - array array intersect objects
     * @param $helpers Helpers - helpers class
     * @param $newEvents array - array of new System Events
     * @param $mode integer - MODE_BOOTSTRAP or MODE_EXPORT
     * @return bool
     */
    public static function createResolver($dir, $intersects, $helpers, $newEvents, $mode = MODE_BOOTSTRAP) {
        /* Create plugin.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating plugin resolver');
            $tpl = $helpers->getTpl('pluginresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[PluginAdapter] pluginresolver tpl is empty');
                return false;
            }

            $fileName = 'plugin.resolver.php';

            if (!file_exists($dir . '/' . $fileName) || $mode == MODE_EXPORT) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);
                $newEventArray = $helpers->beautify($newEvents);
                $tpl = str_replace("'[[+newEvents]]'", $newEventArray, $tpl);
                $helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
        return true;
    }
}