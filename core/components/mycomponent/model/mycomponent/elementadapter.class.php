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

    public function __construct(&$modx, $helpers, $fields, $mode, $object) {
        /* @var $object modElement */
        /*$this->modx =& $modx;
        $this->helpers =& $helpers;*/
        parent::__construct($modx, $helpers);
        if (isset($fields['propertySets'])) {
            $this->setPropertySetResolver($fields['propertySets']);
            unset($fields['propertySets']);
        }
        if ($mode == MODE_BOOTSTRAP) {
            if (is_array($fields)) {
                $this->fieldsToIds($fields);
                $this->myFields = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            if (!$object) {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, '[PluginAdapter] Object is null');
            }
            else {
                $fields = $object->toArray();
                $this->fieldsToNames($fields);
                $this->myFields = $fields;
            }
        }
        ObjectAdapter::$myObjects['ElementCategories'][$fields['category']]['elements'][$this->dbClass][] = $fields;
    }

    public function setPropertySetResolver($sets) {
        foreach ($sets as $k => $setName) {
            $resolverFields = array(
                'element' => isset($fields['element'])
                    ? $fields['element']
                    : $this->getName(),
                'element_class' => isset($fields['element_class'])
                    ? $fields['element_class']
                    : $this->dbClass,
                'property_set' => isset($fields['property_set'])
                    ? $fields['property_set']
                    : $setName,
            );
            ObjectAdapter::$myObjects['propertySetResolver'][] = $resolverFields;
        }
    }

    /* only executes on export */
    public function fieldsToNames(&$fields) {
        /* @var $categoryObj modCategory */
        $categoryObj = $this->modx->getObject('modCategory', $fields['category']);
        if ($categoryObj) {
            $fields['category'] = $categoryObj->get('category');
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find parent for resource: ' . $fields['pagetitle']);
        }
    }
    public function fieldsToIds(&$fields){
        if (isset($fields['category'])) {
            $categoryObj = $this->modx->getObject('modCategory', array('category' => $fields['category']));
            if ($categoryObj) {
                $fields['category'] = $categoryObj->get('id');
            }
        }
    }

    /**
     * Gets the directory containing the code files for the element.
     *
     * @return string - full path for element code file (without filename or
     *         trailing slash)
     */

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
        unset($this->myFields['propertySets']);
        $fields = $this->myFields;
        // core/components/example/elements/snippets/snippet1.snippet.php
        if (isset($fields['static']) && !empty($fields['static'])) {
            $dir = 'core/components/' . $this->helpers->props['packageNameLower'] . '/';
            $path = $this->helpers->getCodeDir($dir, $this->dbClass);
            $path .= '/' . $this->helpers->getFileName($this->getName(), $this->dbClass);
            $this->myFields['source'] = $this->modx->getOption('default_media_source');
            $this->myFields['static_file'] = $path;
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, '    Set static path to ' . $path);
        }
        parent::addToMODx($overwrite);
    }



/* *****************************************************************************
   Export Objects and Support Functions (in MODxObjectAdapter)
***************************************************************************** */

    public function exportObject($element, $overwrite = false) {
    //Perform default export implementation
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