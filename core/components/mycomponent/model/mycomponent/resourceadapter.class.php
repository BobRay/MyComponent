<?php
// Include the Base Class (only once)
require_once('objectadapter.class.php');

class ResourceAdapter extends ObjectAdapter
{//These will never change.

    protected $dbClass = 'modResource';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'pagetitle';
    protected $dbClassParentKey = 'parent';
    protected $createProcessor = 'resource/create';
    protected $updateProcessor = 'resource/update';
    protected $defaults = array();
    protected $name;
    /* @var $helpers Helpers */
    public $helpers;
    /* @var $modx modX */
    public $modx;
// Database Columns for the XPDO Object
    protected $myFields;


    function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $object modResource */
        parent::__construct($modx, $helpers);
        $this->name = $fields['pagetitle'];
        if (! isset($fields['id'])) {
            $fields['id'] = '';
        }
        if ($mode == MODE_BOOTSTRAP) {
    // Set defaults if they are not already set
            $this->defaults['published'] = $modx->getOption('publish_default', null);
            $this->defaults['richtext'] = $modx->getOption('richtext_default',null);
            $this->defaults['hidemenu'] = $modx->getOption('hidemenu_default', null);
            $this->defaults['cacheable'] = $modx->getOption('cache_default', null);
            $this->defaults['searchable'] = $modx->getOption('search_default', null);
            $this->defaults['context'] = $modx->getOption('default_context', null);

            if (!isset($fields['class_key'])) {
                $fields['class_key'] = 'modDocument';
            }
            foreach ($this->defaults as $field => $value) {
                $fields[$field] = isset($fields[$field])
                    ? $fields[$field]
                    : $value;
            }

        } elseif ($mode == MODE_EXPORT) {
                $this->fieldsToNames($fields);
                //unset($fields['id']);
                $this->myFields = $fields;
        }
        $resolverFields = array();
        $resolverFields['pagetitle'] = $fields['pagetitle'];
        $resolverFields['parent'] = isset($fields['parent'])
            ? $fields['parent']
            : 'default';
        $resolverFields['template'] = isset($fields['template'])
            ? $fields['template']
            : 'default';
        if (isset($fields['tvValues'])) {
            $resolverFields['tvValues'] = $fields['tvValues'];
        }
        $this->myFields = $fields;
        unset($fields['tvValues']);

        ObjectAdapter::$myObjects['resourceResolver'][] = $resolverFields;
        ObjectAdapter::$myObjects['resources'][] = $fields;
    }

    /**
     * Converts object fields containing IDs to the names of the objects
     * represented by the IDs -- only executes on export.
     * @param $fields array
     */
    public function fieldsToNames(&$fields) {
        if (!empty($fields['parent'])) {
            $parentObj = $this->modx->getObject('modResource', $fields['parent']);
            if ($parentObj) {
                $fields['parent'] =  $parentObj->get('pagetitle');
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find parent for resource: ' .
                    $fields['parent']);
            }
        }
        if (!empty($fields['template'])) {
            if ($fields['template'] == $this->modx->getOption('default_template')) {
                $fields['template'] = 'default';
            } else {
                $templateObj = $this->modx->getObject('modTemplate', $fields['template']);
                if ($templateObj) {
                    $fields['template'] = $templateObj->get('templatename');
                }
            }
        }
    }

    /**
     * Converts object fields containing names to the IDs of the objects
     * represented by the names.
     * @param $fields array
     */

    public function fieldsToIds(&$fields) {
        if (!isset($fields['parent']) || $fields['parent'] == 'default') {
            $fields['parent'] = '0';
        } else {
            $parentObj = $this->modx->getObject('modResource', array('pagetitle' => $fields['parent']));
            if ($parentObj) {
                $fields['parent'] = $parentObj->get('id');
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find parent for resource: ' . $fields['pagetitle']);
            }
        }
        if (!isset($fields['template']) || empty($fields['template']) || $fields['template'] == 'default') {
            $fields['template'] = $this->modx->getOption('default_template');
        } else {
            $templateObj = $this->modx->getObject('modTemplate', array('templatename' => $fields['template']));
            if ($templateObj) {
                $fields['template'] = $templateObj->get('id');
            }
        }
    }

    /** creates resources in MODX install if set in project config file */
    /*public function newTransport()
    {//Validate Page's Title
        if (empty($this->myFields['pagetitle']))
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'A Resource must have a valid page title!');
            return false;
        }
        
    // Create an alias
        $this->myFields['alias'] = str_replace(' ', '-', strtolower($this->myFields['pagetitle']));

    // Set default Content
        $this->myFields['content'] = 'Enter your page\'s content here';
        
    // Create the Transport File

    }*/

    /**
     * Creates a code file for an element if set in project config file
     *
     * @param $name string - lowercase filename (without extension or type
     * @param $type string - modPlugin, modSnippet etc.
     */
    /*public function newCodeFile($name, $type)
    {   $mc = $this->myComponent;
    
        $dir = $this->helpers->getCodeDir($this->targetCore, $type);
        $fileName = $this->helpers->getFileName($name, $type);
        // echo "\nDIR: " . $dir . "\n" . 'FILENAME: ' . $fileName . "\n" . "TYPE: " . $type . "\n";
        if (empty($fileName)) {
            $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    skipping ' . $type . ' file -- needs no code file');
        } else {
            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->getTpl($type);

                // use 'phpfile.tpl' as default for .php files
                if (empty($tpl) && strstr($fileName, '.php')) {
                    $tpl = $this->getTpl('phpfile.php');
                }
                $tpl = str_replace('[[+elementType]]', strtolower(substr($type,3)), $tpl);
                $tpl = str_replace('[[+elementName]]', $name, $tpl);
                if (!empty ($tpl)) {
                    $tpl = $mc->replaceTags($tpl);
                }
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
            }
        }
    }*/

/* *****************************************************************************
   Import Objects and Support Functions
***************************************************************************** */

    final public function addToMODx($overwrite = false)
    {//Perform default export implementation
        /* @var $modx modX */
        $fields =& $this->myFields;

        $this->fieldsToIds($fields);

        /*if (isset($fields['tvValues'])) {
            $tvValues = $fields['tvValues'];
            unset($fields['tvValues']);
        }*/
        if (!isset($fields['alias']) || empty($fields['alias'])) {
            $fields['alias'] = str_replace(' ', '-', strtolower($fields['pagetitle']));
        }
        $this->myFields = &$fields;
        parent::addToMODx($overwrite);



    }

    public static function createResolver($dir, $intersects, $helpers, $mode = MODE_BOOTSTRAP) {

        /* Create resource.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating resource resolver');
            $tpl = $helpers->getTpl('resourceresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'resourceresolver tpl is empty');
                return false;
            }

            $fileName = 'resource.resolver.php';

            if (!file_exists($dir . '/' . $fileName) || $mode == MODE_EXPORT) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);

                $helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
        return true;
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Processing Resources');
        $resources = ObjectAdapter::$myObjects['resources'];
        parent::createTransportFile($helpers, $resources, '', 'modResource', $mode);
    }

    /**
     * Exports the Resource. Resources work a little differently than most other
     * adapters. Instead, top-level resources call this function recursively. This
     * allows for the tree to grow organically, if there is one.
     *
     * @param $overwrite boolean - (Optional) Allows the function to overwrite the
     *        files. Default value is false.
     *
     * @return boolean - True, if successful; False, if not.
     */
/*    final public function exportObject($object, $overwrite = false)
    {//For Quick Access
        $mc = $this->myComponent;
        $name = $this->getName();
        
    // Perform default export implementation
        if (!parent::exportObject($name))
        {   $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
            return false;
        }
        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
    // Special functionality for Resources
        $this->exportCode($overwrite);
        $this->exportProperties($overwrite);
    // Handle Children
        $this->exportChildren($overwrite);
    // Return Success
        return true;
    }*/


    static function exportResources(&$modx, &$helpers, $props) {
        /* @var $modx modX */
        /* @var $helpers Helpers */
        $objects = array();

        /* Add resources from exportResources array in the project config file
          to $this->myObjects array */
        $helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Exporting Resources');
        $byId = $modx->getOption('getResourcesById', $props, false);
        $method = $byId? 'ID' : 'pagetitle';
        $resources = $modx->getOption('exportResources', $props, array());
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                if ($byId) {
                    $resObject = $modx->getObject('modResource', $resource);
                } else {
                    $resObject = $modx->getObject('modResource', array('pagetitle' => trim($resource)));
                }
                if ($resObject) {
                    $objects[] = $resObject;
                } else {
                    $helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Could not get resource with ' . $method . ': ' . $resource);
                }
            }
        }
        /* if $parents is set in project config, add children (and optionally parent
           to  $resources array */
        $parents = $modx->getOption('parents', $props, array() );
        $includeParents = $modx->getOption('includeParents', $props, false);
        if (!empty($parents)) {
            foreach ($parents as $parentResource) {
                if ($byId) {
                    $parentObj = $modx->getObject('modResource', $parentResource);
                } else {
                    $parentObj = $modx->getObject('modResource', array('pagetitle' => $parentResource));
                }
                if ($parentObj) {
                    if ($includeParents) {
                        $objects[] = $parentObj;
                    }
                    $children = $parentObj->getMany('Children');
                    if (!empty ($children)) {
                        $objects = array_merge($objects, $children);
                    }
                }
            }

        }
        if (!empty($objects)) {
            /* @var $object modResource */
            foreach($objects as $object) {
                $fields = $object->toArray();
                new ResourceAdapter($modx, $helpers, $fields, MODE_EXPORT);
            }
        } else {
            $helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'No Resources found');
        }
    }

    /**
     * Writes the properties file for objects with properties
     *
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    /*public function exportProperties($overwrite = false)
    {//For Quick Access
        $mc = $this->myComponent;
        $dir = $mc->getPath('properties');
        $name = $this->getName();
        $class = $this->getSafeClass();
        $properties = $this->myFields['properties'];
        $fileName = $this->getFileName('properties');
        
        $tpl = $this->getTpl('propertiesfile.tpl');
        $tpl = str_replace('[[+element]]',$name,$tpl);
        $tpl = str_replace('[[+elementType]]', $class, $tpl);
        $tpl = $mc->replaceTags($tpl);

        $hastags = strpos($tpl, '<'.'?'.'php');
        if ($hastags === false)
            $tpl = '<'.'?'.'php'.$tpl;
        $tpl .=  "\n\n" . $this->render_properties($properties) . "\n\n";

        $mc->writeFile($dir, $fileName, $tpl, $this->dryRun);
        
        unset($tpl);
    }*/

    /**
     * Recursive function to write the code for the build properties file. This
     * function has changed from its original, as it checks the passed value. If
     * it is a string, there is no recursion. If not, there is.
     *
     * @param $arr - array of properties
     * @param $depth int - controls recursion
     *
     * @return string - code for the elements properties
     */
    /*public function render_properties($arr, $depth = -1)
    {
    // For Indents
        $tabWidth = 4;
    
        if ($depth == -1) {
            // this will only happen once
            $output = "\$properties = array( \n";
            $depth++;
        } else {
            $output = "array( \n";
        }
        $indent = str_repeat(" ", $depth + $tabWidth );

        foreach( $arr as $key => $val ) 
        {//Ignore List...
            if ($key == 'desc_trans' 
            ||  $key == 'area_trans') 
                continue;
            
            // No key for each property array
            $output .= $depth == 0
                ? $indent 
                : $indent . "'$key' => ";

            if( is_array( $val ) && !empty($val) ) {
                $output .= $this->render_properties( $val, $depth + $tabWidth );
            } else {
                $val = empty($val)? '': $val;
                // see if there are any single quotes
                $qc = "'";
                if (strpos($val,$qc) !== false) {
                    // yes - change outer quote char to "
                    //   and escape all " chars in string
                    $qc = '"';
                    $val = str_replace($qc,'\"',$val);
                }

                $output .= $qc . $val . $qc . ",\n";
            }
        }
        $output .= $depth
            ? $indent . "),\n"
            : "\n);\n\nreturn \$properties;";
        return $output;
    }*/

}