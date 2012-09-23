<?php
// Include the Base Class (only once)
require_once('objectadapter.class.php');

class ResourceAdapter extends ObjectAdapter
{//These will never change.
    final static protected $dbClass = 'modResource';
    final static protected $dbClassIDKey = 'id';
    final static protected $dbClassNameKey = 'pagetitle';
    final static protected $dbClassParentKey = 'parent';
    final static protected $dbTransportAttributes = array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'pagetitle',
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array
        (   'ContentType' => array
            (   xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            ),
        )
    );
    
    static private $init_published = '';
    static private $init_richtext = '';
    static private $init_hidemenu = '';
    static private $init_cacheable = '';
    static private $init_searchable = '';
    static private $init_context = '';
    static private $init_template = '';
    
// Database Columns for the XPDO Object
    protected $myFields;
    protected $myObjects = array();

    final function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myFields = $columns;
            
    // Set defaults if they are not already set
        $modx = $this->myComponent->modx;
        if (empty($init_published))
            $init_published => $modx->getOption('publish_default', null);
        if (empty($init_richtext))
            $init_richtext => $modx->getOption('richtext_default',null);
        if (empty($init_hidemenu))
            $init_hidemenu => $modx->getOption('hidemenu_default', null);
        if (empty($init_cacheable))
            $init_cacheable => $modx->getOption('cache_default', null);
        if (empty($init_searchable))
            $init_searchable => $modx->getOption('search_default', null);
        if (empty($init_context))
            $init_context => $modx->getOption('default_context', null);
        if (empty($init_template))
            $init_template => $modx->getOption('default_template', null);
    }
    
/* *****************************************************************************
   Bootstrap and Support Functions
***************************************************************************** */
    /** creates resources in MODX install if set in project config file */
    public function newTransport() 
    {//Validate Page's Title
        if (empty($this->myFields['pagetitle']))
        {   $this->myComponent->sendLog(MODX::LOG_LEVEL_INFO, 'A Resource must have a valid page title!')
            return false;
        }
        
    // Create an alias
        $this->myFields['alias'] = str_replace(' ', '-', strtolower($this->myFields['pagetitle']));

    // Set default properties
        $this->myFields['published'] => $init_published;
        $this->myFields['richtext'] => $init_richtext;
        $this->myFields['hidemenu'] => $init_hidemenu;
        $this->myFields['cacheable'] => $init_cacheable;
        $this->myFields['searchable'] => $init_searchable;
        $this->myFields['context'] => $init_context;
        $this->myFields['template'] => $init_template;

    // Set default Content
        $this->myFields['content'] = 'Enter your page\'s content here';
        
    // Create the Transport File
        if (parent::newTransport())
        // Create the Code File
            $this->newCodeFile();
    }

    /**
     * Creates a code file for an element if set in project config file
     *
     * @param $name string - lowercase filename (without extension or type
     * @param $type string - modPlugin, modSnippet etc.
     */
    public function newCodeFile($name, $type) 
    {   $mc = $this->myComponent;
    
        $dir = $this->helpers->getCodeDir($this->targetCore, $type);
        $fileName = $this->helpers->getFileName($name, $type);
        // echo "\nDIR: " . $dir . "\n" . 'FILENAME: ' . $fileName . "\n" . "TYPE: " . $type . "\n";
        if (empty($fileName)) {
            $mc->sendLog(MODX::LOG_LEVEL_INFO, '    skipping ' . $type . ' file -- needs no code file');
        } else {
            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->getTpl($type);

                /* use 'phpfile.tpl' as default for .php files */
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
                $mc->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
            }
        }
    }

/* *****************************************************************************
   Import Objects and Support Functions
***************************************************************************** */

    final public function addToMODx($overwrite = false)
    {//Perform default export implementation
        $id = parent::addToMODx($overwrite);
    // If MODx accepted the object
        if ($id)
        {//Set the new ID
            $this->myFields[self::dbClassIDKey] = $id;
            attachTemplate();
        // Account for children resources
            $children = $this->myObjects();
            foreach ($children as $child)
            {//Link the child and parent in database
                $child->myFields[get_class($child)::dbClassParentKey] = $id;
                $child->addToMODx($overwrite);
            }
        }
    }

    /**
     * Connects Resources to package templates and creates a resolver to
     * connect them during the install.
     */
    public function attachTemplate() 
    {//For Quick Access
        $mc = $this->myComponent;
        $modx = $mc->modx;
        $dir = $mc->getPath('resolve');
    
        $data = $modx->getOption('resourceTemplates', $this->props, '');
        $mc->createIntersects($data, 'resourceTemplates', 'modTemplate', 'modResource','','');
        /* Create resource.resolver.php resolver */
        if (!empty($data)) {
            $mc->sendLog(MODX::LOG_LEVEL_INFO, 'Creating resource resolver');
            $tpl = $this->getTpl('resourceresolver.php');
            $tpl = $mc->replaceTags($tpl);
            if (empty($tpl)) {
                $mc->sendLog(MODX::LOG_LEVEL_ERROR, 'resourceresolver tpl is empty');
            }
            
            $fileName = 'resource.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->getTpl('resourceresolvercode.php');
                $codeTpl = str_replace('<?php', '', $codeTpl);

                foreach ($data as $template => $resources) {
                    $tempCodeTpl = str_replace('[[+template]]', $template, $codeTpl);
                    $tempCodeTpl = str_replace('[[+resources]]', $resources, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $mc->writeFile($dir, $fileName, $tpl);
            } else {
                $mc->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }

/* *****************************************************************************
   Export Objects and Support Functions
***************************************************************************** */

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
    final public function exportObject($overwrite = false)
    {//For Quick Access
        $mc = $this->myComponent;
        $name = $this->getName();
        
    // Perform default export implementation
        if (!parent::exportObject())
        {   $mc->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
            return false;
        }
        $mc->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
    // Special fuctionality for Resources
        $this->exportCode($overwrite);
        $this->exportProperties($overwrite);
    // Handle Children
        $this->exportChildren($overwrite);
    // Return Success
        return true;
    }

    /** Deprecated: Function moved to exportChildren. Implementation completely replaced. */
    private function pullResources() {    }

    /** 
     * Queries the MODx Installation for child Resources and exports all of them.
     *
     * @param $overwrite boolean - (Optional) Allows the function to overwrite the
     *        files. Default value is false.
     */
    protected function exportChildren($overwrite = false) 
    {//For Quick Access
        $mc = $this->myComponent;
        $modx = $mc->modx;
    
    // We DO NOT trust project.config for exporting.
        $tempObj = array();
        $children = $modx->getCollection($this->getClass(), array('parent' => $this->getKey()));
        if (!empty($children))
            foreach ($children as $child)
                if (!empty($child))
                {   $obj = new ResourceAdapter($mc);
                    $obj->myFields = $child->toArray();
                    $tempObj[] = $obj;
                }
    // Clean up some memory
        unset $children
        
    // Export all VALID Children
        foreach ($tempObj as $obj)
            if (!empty($obj))
                $obj->exportObject($overwrite)

    // Align the Children array
        $this->myObjects = $tempObj;
    }

    /**
     * Writes the properties file for objects with properties
     *
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    public function exportProperties($overwrite = false) 
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
    }

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
    public function render_properties($arr, $depth = -1) 
    {
    // For Indents
        $tabWidth = 4;
    
        if ($depth == -1) {
            /* this will only happen once */
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
            
            /* No key for each property array */
            $output .= $depth == 0
                ? $indent 
                : $indent . "'$key' => ";

            if( is_array( $val ) && !empty($val) ) {
                $output .= $this->render_properties( $val, $depth + $tabWidth );
            } else {
                $val = empty($val)? '': $val;
                /* see if there are any single quotes */
                $qc = "'";
                if (strpos($val,$qc) !== false) {
                    /* yes - change outer quote char to "
                       and escape all " chars in string */
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
    }

/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */
    final public function buildVehicle()
    {//Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $this->myComponent->sendLog(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->myFields['pagetitle']);
            return true;
        }
    }
}