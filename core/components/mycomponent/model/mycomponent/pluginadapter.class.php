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

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP, $object = null) {
        /* @var $object modPlugin */
        $this->name = $fields['name'];
        if (isset($fields['events'])) {
            $this->setPluginResolver($fields['events']);
            unset($fields['events']);
        }
        parent::__construct($modx, $helpers, $fields, $mode, $object);


    }
    public function setPluginResolver($events) {
        foreach ($events as $eventName => $fields) {
            $resolverFields = array(
                'pluginid' => $this->getName(),
                'event' => isset($fields['event']) ? $fields['event'] : $eventName,
                'priority' => isset($fields['priority']) && !empty($fields['priority'])? $fields['priority'] : '0',
                'propertyset' => isset($fields['propertySet']) && !empty($fields['priority']) ? $fields['propertySet'] : '0',
            );
            ObjectAdapter::$myObjects['pluginResolver'][] = $resolverFields;
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
     * @return bool
     */
    public static function createResolver($dir, $intersects, $helpers, $newEvents) {
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

            if (!file_exists($dir . '/' . $fileName)) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);
                $newEventArray = $helpers->beautify($newEvents);
                $tpl = str_replace("'[[+newEvents]]'", $newEventArray, $tpl);
                $helpers->writeFile($dir, $fileName, $tpl);
            }
            else {
                $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
        return true;
    }
    /** NOT USED
     * Connects System Events to Plugins and creates resolver for connecting them
     *  during the build if set in the project config file */
    protected function attachEvents()
    {//For Quick Access
        /* create the resolver */
        $pluginEvents = $modx->getOption('pluginEvents', $this->props, array());
        if (! empty($pluginEvents)) {
            $myComponent->sendLog(MODX::LOG_LEVEL_INFO, 'Creating plugin resolver');
            $tpl = $this->helpers->getTpl(('pluginresolver.php'));
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $myComponent->sendLog(MODX::LOG_LEVEL_ERROR, '[PluginAdapter] pluginresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'plugin.resolver.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $code = '';
    
                $codeTpl = $this->helpers->getTpl('pluginresolvercode.php');
                if (empty($codeTpl)) {
                    $myComponent->sendLog(MODX::LOG_LEVEL_ERROR, '[PluginAdapter] pluginresolvercode tpl is empty');
                }
                $codeTpl = str_replace('<?php', '', $codeTpl);
    
                foreach($pluginEvents as $plugin => $events) {
                        $tempCodeTpl = str_replace('[[+plugin]]', $plugin, $codeTpl);
                        $tempCodeTpl = str_replace('[[+events]]', $events, $tempCodeTpl);
                        $code .= "\n" . $tempCodeTpl;
                }
                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $newEvents = $this->props['newSystemEvents'];
                $removeTpl = '';
                if (!empty($newEvents)) {
                    $removeTpl = $this->helpers->getTpl('removenewevents.php');
                    $removeTpl = str_replace('<?php', '', $removeTpl);

                }
                $tpl = str_replace('/* [[+remove_new_events]] */', $removeTpl, $tpl);
                $tpl = str_replace('[[+category]]', $this->props['category'], $tpl);
                $tpl = str_replace('[[+newEvents]]', $newEvents, $tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $myComponent->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }

/* *****************************************************************************
   Export Objects and Support Functions (in ElementAdapter)
***************************************************************************** */

/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */
    final public function buildVehicle()
    {//Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->properties['pagetitle']);
            return true;
        }
    }
}