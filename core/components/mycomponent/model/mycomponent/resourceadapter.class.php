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
    protected $resourceId = 0;
    protected $name;
    /* @var $helpers Helpers */
    public $helpers;
    /* @var $modx modX */
    public $modx;

// Database Columns for the XPDO Object
    protected $myFields;
    protected $myObjects = array();

    final function __construct(&$modx, &$helpers, &$fields) {
        /* @var $modx modX */
        $this->modx =& $modx;
        $this->helpers =& $helpers;
        $this->name = $fields['pagetitle'];

        parent::__construct($this->modx, $this->helpers);

            
    // Set defaults if they are not already set
        //$modx =& $mc->modx;
        //$this->modx =&  $mc->modx;

        $this->defaults['published'] = $modx->getOption('publish_default', null);
        $this->defaults['richtext'] = $modx->getOption('richtext_default',null);
        $this->defaults['hidemenu'] = $modx->getOption('hidemenu_default', null);
        $this->defaults['cacheable'] = $modx->getOption('cache_default', null);
        $this->defaults['searchable'] = $modx->getOption('search_default', null);
        $this->defaults['context'] = $modx->getOption('default_context', null);
        $this->defaults['template'] = $modx->getOption('default_template', null);

        if (!isset($fields['class_key'])) {
            $fields['class_key'] = 'modDocument';
        }

        foreach ($this->defaults as $field => $value) {
            $fields[$field] = isset($fields[$field])
                ? $fields[$field]
                : $value;
        }

        $this->myFields = $fields;
    }

   /* public function getName() {
        return $this->name;
    }

    public function getProcessor($mode) {
        return $mode == 'create'
            ? $this->createProcessor
            : $this->updateProcessor;
    }*/
/* *****************************************************************************
   Bootstrap and Support Functions
***************************************************************************** */
    /** creates resources in MODX install if set in project config file */
    public function newTransport() 
    {//Validate Page's Title
        if (empty($this->myFields['pagetitle']))
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'A Resource must have a valid page title!');
            return false;
        }
        
    // Create an alias
        $this->myFields['alias'] = str_replace(' ', '-', strtolower($this->myFields['pagetitle']));

    // Set default properties
        /*$this->myFields['published'] = $init_published;
        $this->myFields['richtext'] = $init_richtext;
        $this->myFields['hidemenu'] = $init_hidemenu;
        $this->myFields['cacheable'] = $init_cacheable;
        $this->myFields['searchable'] = $init_searchable;
        $this->myFields['context'] = $init_context;
        $this->myFields['template'] = $init_template;*/

    // Set default Content
        $this->myFields['content'] = 'Enter your page\'s content here';
        
    // Create the Transport File
        if (parent::newTransport())
        // Create the Code File
            $this->newCodeFile($this->myFields['pagetitle'], 'modResource');
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
            $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    skipping ' . $type . ' file -- needs no code file');
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
                $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
            }
        }
    }

/* *****************************************************************************
   Import Objects and Support Functions
***************************************************************************** */

    final public function addToMODx($overwrite = false)
    {//Perform default export implementation
        /* @var $modx modX */
        $fields =& $this->myFields;
        $tvValues = array();

        if (! is_numeric($fields['template'])) { /* user sent a template name */
            /* @var $templateObj modTemplate */
            $templateName = $fields['template'];
            $templateObj = $this->modx->getObject('modTemplate', array('templateName'=> $fields['template']));
            if ($templateObj) {
                $fields['template'] = $templateObj->get('id');
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find template: ' . $templateName);
                $fields['template'] = $this->defaults['template'];
            }

        }
        if (isset($fields['parent'])) {
            /* @var $parentObj modResource */
            $parentName = $fields['parent'];
            $parentObj = $this->modx->getObject('modResource', array('pagetitle' => $parentName));
            if ($parentObj) {
                $fields['parent'] = $parentObj->get('id');
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find parent resource: ' . $parentName);
                $fields['parent'] = 0;
            }

        }
        if (isset($fields['tvValues'])) {
            $tvValues = $fields['tvValues'];
            unset($fields['tvValues']);
        }
        if (!isset($fields['alias']) || empty($fields['alias'])) {
            $fields['alias'] = str_replace(' ', '-', strtolower($fields['pagetitle']));
        }
        $this->myFields = &$fields;
        $obj = parent::addToMODx($overwrite);


    // If MODx accepted the object
        if ($obj && $obj instanceof modResource) {
            /* Set the new ID */
            $this->resourceId = $obj->get('id');

            if (!empty ($tvValues)) {
                $this->attachTvs($obj, $tvValues);
            }
        }
    }


    /*  NOT USED
     * Connects Resources to package templates and creates a resolver to
     * connect them during the install.
     */
    public function attachTemplate(&$resourceObj, $templateName) {
        /* @var $modx modX */
        /* @var $mc MyComponentProject */
        /* @var $resourceObj modResource */

        $mc =& $this->myComponent;
        $modx =& $mc->modx;

        /* Set resource Template */
        if (!empty($templateName)) {
            $template = $modx->getObject('modTemplate', array('templatename' => $templateName));
            if ($template) {
                $templateId = $template->get('id');
                $resourceObj->set('template', $templateId);
                if ($resourceObj->save()) {
                    $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '         Connected Resource ' .
                        $resourceObj->get('pagetitle') . ' to ' .
                        $templateName . ' Template');
                }

            } else {
                $mc->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '        Could not find template: ' . $templateName);
            }
        }

        // $data = $modx->getOption('resourceTemplates', $this->props, '');
        // $mc->createIntersects($data, 'resourceTemplates', 'modTemplate', 'modResource','','');

        return;

        /* resource resolver will be created elewhere - this object will return it's own bit
          of the resolver code in another function
        */

        /* Create resource.resolver.php resolver */
        $dir = $mc->getPath('resolve');
        if (!empty($data)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating resource resolver');
            $tpl = $this->getTpl('resourceresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'resourceresolver tpl is empty');
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
                $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }
    /* Not used */
    public function attachParent(&$obj, $parentName) {

    }
    public function attachTvs(&$obj, $tvValues) {
        /* @var $obj modResource */
        foreach($tvValues as $k => $v) {
            $obj->setTVValue($k, $v);

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
        if (!parent::exportObject($name))
        {   $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
            return false;
        }
        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: ' . $name);
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
        /* @var $modx modX */
        $mc = $this->myComponent;
        $modx =& $mc->modx;
    
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
        unset ($children);
        
    // Export all VALID Children
        foreach ($tempObj as $obj)
            if (!empty($obj))
                $obj->exportObject($overwrite);

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