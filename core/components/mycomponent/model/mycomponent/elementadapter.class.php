<?php

abstract class ElementAdapter extends ObjectAdapter {

    protected $categoryId;
    // Database fields for the XPDO Object
    protected $myFields;
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;

    /* *****************************************************************************
       Property Getter and Setters
    ***************************************************************************** */
    /**
     * Gets the directory containing the code files for the element.
     *
     * @return string - full path for element code file (without filename or 
     *         trailing slash)
     */

    public function __construct(&$modx, $helpers) {
        $x = 1;
        if (isset($this->myFields['category'])) {
            /*$category = $this->myFields['category'];
            $categoryId = $category;*/
            $this->modx =& $modx;
            $this->helpers =& $helpers;
            /*if (!is_numeric($category)) {
                $categoryObj = $this->modx->getObject('modCategory', array('category' => $category));
                if (!$categoryObj) {
                    $categoryObj = $this->modx->newObject('modCategory', array('category' => $category));

                    if ($categoryObj && $categoryObj->save()) {
                        $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    Created new category ' . $category);
                    }
                }
                $categoryId = $categoryObj
                    ? $categoryObj->get('id')
                    : 0;
            }
            $this->myFields['category'] = $categoryId;*/
        }


        parent::__construct($modx, $helpers);


    }
    public function getCodeDir() 
    {//Get the path...
        $path = $this->myComponent->myPaths['targetCore'] . 'elements/';
    // Get the sub-directory according to type...
        $type = $this->getClass();
        $type = $type == 'modTemplateVar' ? 'modTv' : $type;
        $type = strtolower(substr($type, 3) . 's');
    // Append slash and return
        return $path . $type . '/';
    }


/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */


/* *****************************************************************************
   Import Objects and Support Functions (in MODxObjectAdapter) 
***************************************************************************** */

    public function addToMODx($overwrite = false) {
        if (isset($this->myFields['propertySets'])) {
            $pSets = $this->myFields['propertySets'];
            if (is_array($pSets)) {
                foreach($pSets as $k => $pName ) {
                    $fields = array('name' => $pName);
                    $o = new PropertySetAdapter($this->modx, $this->helpers, $fields);
                    $o->addToModx();
                }

            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Property Sets listed under ' . $this->name . ' must be an array');
            }

            unset($this->myFields['propertySets']);
        }

        parent::addToMODx($overwrite);
    }

    /**
     * Creates a MODX element object in the DB if set in project config file
     *
     * @param $name string - name of object in MODX install
     * @param $type string - modSnippet, modChunk, etc.
     */
   /* public function attachCategory()
    {//For Quick Access
        $myComponent = $this->myComponent;
        $modx = $myComponent->modx;
        $type = static::xPDOClass;
        $nameKey = static::xPDOClassNameKey;
        $nameValue = $this->myColumns[$nameKey];
        

        $lName =strtolower($nameValue);
        $alias = $type == 'modTemplate'? 'templatename' : 'name';
        $obj = $modx->getObject($type, array($nameKey => $nameValue));
        
        if ($obj) 
        {   $obj->set('category', $this->categoryId);
            if ($obj->save())
                $myComponent->sendLog(MODX::LOG_LEVEL_INFO, 'Attached ' . $type . ': ' . $name .  ' to Category (' . $this->categoryId . ')');
            else
                $myComponent->sendLog(MODX::LOG_LEVEL_INFO, 'Failed to attach ' . $type . ': ' . $name .  ' to Category (' . $this->categoryId . ')');
        }
    }*/

/* *****************************************************************************
   Export Objects and Support Functions (in MODxObjectAdapter)
***************************************************************************** */

    public function exportObject()
    {//Perform default export implementation
        if (!parent::exportObject())
            return false;
    // Special fuctionality for ALL Elements
        if (static::xPDOClass != 'modCategory') {
            exportCode();
            exportProperties();
        }
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
    public function exportCode ($elementObj, $element) {

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

    /**
     * Writes the properties file for objects with properties
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    public function exportProperties($properties, $fileName, $objectName)
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
}