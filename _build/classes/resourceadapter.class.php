<?php
// Include the Base Class (only once)
require_once('modxobjectadapter.class.php');

class ResourceAdapter extends MODxObjectAdapter
{//These will never change.
    final static protected $xPDOClass = 'modResource';
    final static protected $xPDOClassParentKey = 'parent';
    final static protected $xPDOTransportAttributes = array(
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
    protected $myColumns;

    final function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myColumns = $columns;
            
    // Set defaults if they are not already set
        $modx = $myComponent->modx;
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
        if (empty($this->myColumns['pagetitle']))
        {   $myComponent->log(MODX::LOG_LEVEL_INFO, 'A Resource must have a valid page title!')
            return false;
        }
        
    // Create an alias
        $this->myColumns['alias'] = str_replace(' ', '-', strtolower($this->myColumns['pagetitle']));

    // Set default properties
        $this->myColumns['published'] => $init_published;
        $this->myColumns['richtext'] => $init_richtext;
        $this->myColumns['hidemenu'] => $init_hidemenu;
        $this->myColumns['cacheable'] => $init_cacheable;
        $this->myColumns['searchable'] => $init_searchable;
        $this->myColumns['context'] => $init_context;
        $this->myColumns['template'] => $init_template;

    // Set default Content
        $this->myColumns['content'] = 'Enter your page\'s content here';
        
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
    public function newCodeFile($name, $type) {
        $dir = $this->helpers->getCodeDir($this->targetCore, $type);
        $fileName = $this->helpers->getFileName($name, $type);
        // echo "\nDIR: " . $dir . "\n" . 'FILENAME: ' . $fileName . "\n" . "TYPE: " . $type . "\n";
        if (empty($fileName)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    skipping ' . $type . ' file -- needs no code file');
        } else {
            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($type);

                /* use 'phpfile.tpl' as default for .php files */
                if (empty($tpl) && strstr($fileName, '.php')) {
                    $tpl = $this->helpers->getTpl('phpfile.php');
                }
                $tpl = str_replace('[[+elementType]]', strtolower(substr($type,3)), $tpl);
                $tpl = str_replace('[[+elementName]]', $name, $tpl);
                if (!empty ($tpl)) {
                    $tpl = $this->helpers->replaceTags($tpl);
                }
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
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
            $this->myColumns[self::xPDOClassIDKey] = $id;
        // Account for children resources
            foreach ($children as $child)
            {//Link the child and parent in database
                $child->myColumns[get_class($child)::xPDOClassParentKey] = $id;
                $child->addToMODx($overwrite);
            }
        }
    }

/* *****************************************************************************
   Export Objects and Support Functions
***************************************************************************** */

    final public function exportObject()
    {//Perform default export implementation
        if (!parent::exportObject())
        {   $myComponent->log(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: '.$this->myColumns['pagetitle']);
            return false;
        }
    // Special fuctionality for Resources
        exportCode();
        exportProperties();
    // Handle Children
        pullResources();
    // Return Success
        $myComponent->log(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: '.$this->myColumns['pagetitle']);
        return true;
    }

    /**
     * Creates the code file for an element or resource - skips static elements
     *
     * @param $elementObj modElement - element MODX object
     * @param $element - string name of element type ('plugin', 'snippet' etc.) used in dir name.
     */
    private function exportCode ($elementObj, $element) {

        /* @var $elementObj modElement */

        if ($elementObj->get('static')) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Skipping object file for static object: ' . $elementObj->get('name'));
            return;
        }
        $type = $this->elementType;
        $name = $elementObj->get($this->helpers->getNameAlias($type));

        $fileName = $this->helpers->getFileName($name, $type);
        if ($fileName) {
            $content = $elementObj->getContent();
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Skipping object file for: ' . $type . '; object (does not need source file)');
            return;
        }
        if ($type == 'modResource') {
            $dir = $this->resourcePath;
        } else {
            $dir = $this->helpers->getCodeDir($this->targetCore, $type);
        }
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '    Would be creating: ' . $fileName . "\n");
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
        }
        $tpl = '';
        if ($type == 'modSnippet' || $type == 'modPlugin') {
            if (! strstr($content, '<?')) {
                $tpl .= '<'.'?'.'php'."\n\n";
                //fwrite($fileFp,"<?php\n\n");
            }
            /* add header if it's not already there */
            if ( (!strstr($content,'GNU')) && (!stristr($content,'License')) ) {
                $tpl = $this->helpers->getTpl('phpfile.php');
                $tpl = str_replace('[[+elementName]]', $elementObj->get('name'), $tpl);
                $tpl = str_replace('[[+elementType]]', substr(strtolower($this->elementType), 3), $tpl);
                $tpl = $this->helpers->replaceTags($tpl);
            }
        }
        $tpl .= $content;

        $this->helpers->writeFile($dir, $fileName, $tpl, $this->dryRun);
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
        }
        unset($tpl);
    }

    /** populates $this->elements with an array of resources based on pagetitles and/or parents */
    protected function pullResources() {
        /* @var $parent modResource */
        $this->elements = array();

        /* add resources from pagetitle array to $this->elements */
        if (!empty($this->pagetitles)) {
            foreach ($this->pagetitles as $pagetitle) {
                $resObject = $this->modx->getObject('modResource', array('pagetitle' => trim($pagetitle)));
                if ($resObject) {
                    $this->elements[] = $resObject;
                } else {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not get resource with pagetitle: ' . $pagetitle);
                }
            }
        }
        /* add children of pagetitle array objects to $this->elements */
        if (!empty($this->parents)) {
            foreach($this->parents as $parentId) {
                $parent = $this->modx->getObject('modResource', $parentId);
                if ($parent) {
                    if ($this->includeParents) {
                        $this->elements[] = $parent;
                    }
                    $children = $parent->getMany('Children');
                    if (!empty ($children)) {
                        $this->elements = array_merge($this->elements,$children);
                    }
                }
            }
        }
    }

    /**
     * Writes the properties file for objects with properties
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    private function exportProperties($properties, $fileName, $objectName) 
    {   $dir = $this->transportPath . 'properties/';
        $tpl = $this->helpers->getTpl('propertiesfile.tpl');
        $tpl = str_replace('[[+element]]',$objectName,$tpl);
        $tpl = str_replace('[[+elementType]]', substr(strtolower($this->elementType), 3), $tpl);

        $tpl = $this->helpers->replaceTags($tpl);
        $hastags = strpos($tpl, '<'.'?'.'php');
        if ($hastags === false)
            $tpl = '<'.'?'.'php'.$tpl;
        $tpl .=  "\n\n" . $this->render_properties($properties) . "\n\n";

        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Would be creating: ' . $fileName . "\n");
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
        }
        $this->helpers->writeFile($dir, $fileName, $tpl, $this->dryRun);
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
        }
        unset($tpl);
    }

    /**
     * Recursive function to write the code for the build properties file.
     *
     * @param $arr - array of properties
     * @param $depth int - controls recursion
     * @param int $tabWidth - tab width for code (uses spaces)
     * @return string - code for the elements properties
     */
    private function render_properties( $arr, $depth=-1, $tabWidth=4) {

        if ($depth == -1) {
            /* this will only happen once */
            $output = "\$properties = array( \n";
            $depth++;
        } else {
            $output = "array( \n";
        }
        $indent = str_repeat( " ", $depth + $tabWidth );

        foreach( $arr as $key => $val ) {
            if ($key=='desc_trans' || $key == 'area_trans') {
                continue;
            }
            /* No key for each property array */
            $output .= $depth == 0? $indent : $indent . "'$key' => ";

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
        $output .= $depth?
            $indent . "),\n"
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
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: '.$this->myColumns['pagetitle']);
            return true;
        }
    }
}