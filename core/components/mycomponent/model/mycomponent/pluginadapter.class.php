<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class PluginAdapter extends ElementAdapter
{
    final static protected $xPDOClass = 'modPlugin';
    static protected $xPDOClassIDKey = 'id';
    static protected $xPDOClassNameKey = 'name';
    static protected $xPDOClassParentKey = 'category';
    
// Database Columns for the XPDO Object
    protected $myParent;
    protected $myColumns;

    final public function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myColumns = $columns;
    }
    
/* *****************************************************************************
   Bootstrap and Support Functions (in ElementAdapter)
***************************************************************************** */

/* *****************************************************************************
   Import Objects and Support Functions (in ElementAdapter) 
***************************************************************************** */

    protected function addToMODx($overwrite = false)
    {//Perform default export implementation
        $id = parent::addToMODx($overwrite);
    // Sets the ID appropriately, for later.
        $this->myColumns[static::xPDOClassIDKey] = $id;
    }

    /** Connects System Events to Plugins and creates resolver for connecting them
     *  during the build if set in the project config file */
    protected function attachEvents() 
    {//For Quick Access
        $modx = $myComponent->modx;
        $type = static::xPDOClass;
        $nameKey = static::xPDOClassNameKey;
        $nameValue = $this->myColumns[$nameKey];
        
        /* @var $object modElement */
        $lName =strtolower($nameValue);
        $alias = $type == 'modTemplate'? 'templatename' : 'name';
        $obj = $modx->getObject($type, array($nameKey => $nameValue));

        $pluginEvents = $modx->getOption('pluginEvents', $this->props, array());
        
        $this->helpers->createIntersects($pluginEvents, 'modPluginEvent', 'modPlugin', 'modSystemEvent', 'pluginid', 'event');

        /* create the resolver */
        $pluginEvents = $modx->getOption('pluginEvents', $this->props, array());
        if (! empty($pluginEvents)) {
            $myComponent->sendLog(MODX::LOG_LEVEL_INFO, 'Creating plugin resolver');
            $tpl = $this->helpers->getTpl(('pluginresolver.php'));
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $myComponent->sendLog(MODX::LOG_LEVEL_ERROR, 'pluginresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'plugin.resolver.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $code = '';
    
                $codeTpl = $this->helpers->getTpl('pluginresolvercode.php');
                if (empty($codeTpl)) {
                    $myComponent->sendLog(MODX::LOG_LEVEL_ERROR, 'pluginresolvercode tpl is empty');
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