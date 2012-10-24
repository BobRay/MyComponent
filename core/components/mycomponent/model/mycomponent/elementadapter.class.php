<?php

abstract class ElementAdapter extends ObjectAdapter {

    protected $categoryId;
    // Database fields for the XPDO Object
    protected $myFields;
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;
    public $categoryName;
    public $content = null;  /* Content field contents */


    public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {

        $this->modx =& $modx;
        $this->helpers =& $helpers;

        if (!isset($fields['id'])) {
            $fields['id'] = '';
        }

        parent::__construct($modx, $helpers);
        if ($this->dbClass != 'modPropertySet') {
            $this->setPropertySetResolver($fields, $mode);
        }
        if (isset($fields['propertySets'])) {
            unset($fields['propertySets']);
        }
        if ($mode == MODE_BOOTSTRAP) {
            if (is_array($fields)) {
                $this->categoryName = $fields['category'];
                $this->fieldsToIds($fields);
                $this->myFields = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            if ($this->dbClass !== 'modPropertySet')  {
                $this->content = $fields['content'];
                unset($fields['content']);
            }
            $this->fieldsToNames($fields);
            $this->categoryName = $fields['category'];
            $this->myFields = $fields;
        }
        ObjectAdapter::$myObjects['ElementCategories'][$this->categoryName]['elements'][$this->dbClass][] = $fields;
    }

    public function setPropertySetResolver($fields, $mode) {
        if ($mode == MODE_BOOTSTRAP) {
            if (! isset($fields['propertySets'])) {
                return;
            } else {
                $sets = $fields['propertySets'];
            }
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
        } elseif ($mode == MODE_EXPORT) {
            $alias = $this->helpers->getNameAlias($this->dbClass);
            $me = $this->modx->getObject($this->dbClass, array($alias => $this->getName()));
            /* @var $me modElement */
            if (!$me) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, "[ElementAdapter] Can't find myself");
            } else {
                $eps = $me->getMany('PropertySets');
                if (!empty($eps)) {
                   foreach ($eps as $ep) {
                       /* @var $ep modElementPropertySet */
                       $fields = $ep->toArray();
                       /* @var $propertySetObj modPropertySet */
                       $propertySetObj = $this->modx->getObject('modPropertySet',
                           $fields['property_set']);
                       $propertySetName = $propertySetObj->get('name');
                       $resolverFields = array(
                           'element' => $this->getName(),
                           'property_set' => $propertySetName,
                           'element_class' => $this->dbClass,
                       );
                       ObjectAdapter::$myObjects['propertySetResolver'][] = $resolverFields;
                   }
                }
            }
        }
    }



    /**
     * Converts object fields containing IDs to the names of the objects
     * represented by the IDs -- only executes on export.
     * @param $fields array
     */
    public function fieldsToNames(&$fields) {
        /* @var $categoryObj modCategory */
        $categoryObj = $this->modx->getObject('modCategory', $fields['category']);
        if ($categoryObj) {
            $fields['category'] = $categoryObj->get('category');
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[Element Adapter] Could not find category with name: ' . $fields['category']);
        }
    }

    /**
     * Converts object fields containing names to the IDs of the objects
     * represented by the names.
     * @param $fields array
     */

    public function fieldsToIds(&$fields){
        if (isset($fields['category'])) {
            $categoryObj = $this->modx->getObject('modCategory', array('category' => $fields['category']));
            if ($categoryObj) {
                $fields['category'] = $categoryObj->get('id');
            }
        }
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */

        $categories = ObjectAdapter::$myObjects['ElementCategories'];
        if (empty($categories)) {
            $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    No Elements to process');
            return;
        }

        foreach($categories as $category => $elementList) {
            $category = strtolower($category);
            $helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Processing category: ' . $category);
            foreach($elementList['elements'] as $type => $elements) {
                $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Processing ' . $type);
                parent::createTransportFile($helpers, $elements, $category, $type, $mode);
                foreach($elements as $k => $fields ) {
                    $alias = $helpers->getNameAlias($type);
                    $helpers->sendLog(MODX::LOG_LEVEL_INFO, '        Processing object: ' . $fields[$alias]);

                }
            }
        }

    }
    /**
     * Gets the directory containing the code files for the element.
     *
     * @return string - full path for element code file (without filename or
     *         trailing slash)
     */

    /*public function getCodeDir()
    {//Get the path...
        $path = $this->myComponent->myPaths['targetCore'] . 'elements/';
    // Get the sub-directory according to type...
        $type = $this->getClass();
        $type = $type == 'modTemplateVar' ? 'modTv' : $type;
        $type = strtolower(substr($type, 3) . 's');
    // Append slash and return
        return $path . $type . '/';
    }*/


/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */


/* *****************************************************************************
   Import Objects and Support Functions (in MODxObjectAdapter) 
***************************************************************************** */

    public function addToMODx($overwrite = false) {
        unset($this->myFields['propertySets']);
        $fields = $this->myFields;

        if (isset($fields['static']) && !empty($fields['static'])) {
            $projectDir = str_replace(MODX_ASSETS_PATH . 'mycomponents/',
                '',$this->helpers->props['targetRoot']);
            $dir = 'assets/mycomponents/';
            $dir .= $projectDir . 'core/components/' .
                $this->helpers->props['packageNameLower'] . '/';
            $path = $this->helpers->getCodeDir($dir, $this->dbClass);
            $path .= '/' . $this->helpers->getFileName($this->getName(), $this->dbClass);
            $this->myFields['source'] = $this->modx->getOption('default_media_source');
            $this->myFields['static_file'] = $path;
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Set static path to ' . $path);
        }
        parent::addToMODx($overwrite);
    }


   /* public function exportObject($element, $overwrite = false) {
    //Perform default export implementation
        if (!parent::exportObject())
            return false;
    // Special functionality for ALL Elements
        if (static::xPDOClass != 'modCategory') {
            exportCode();
            exportProperties();
        }
    // Return Success
        $myComponent->log(modX::LOG_LEVEL_INFO, 'Transport File created for Resource: '.$this->myColumns['pagetitle']);
        return true;
    }*/
    
    /**
     * Creates the code file for an element or resource - skips static elements
     *
     * @param $elementObj modElement - element MODX object
     * @param $element - string name of element type ('plugin', 'snippet' etc.) used in dir name.
     */
    /*public function exportCode ($elementObj, $element) {

        // @var $elementObj modElement

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
            // add header if it's not already there
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
    }*/


    /**
     * Writes the properties file for objects with properties
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    public function writePropertiesFile($objectName, $properties, $mode = MODE_BOOTSTRAP, $dryRun = false) {
        $dir = $this->helpers->props['targetRoot'] . '_build/data/properties/';
        $fileName = $this->helpers->getFileName($this->getName(),
            $this->dbClass, 'properties');
        if (file_exists($dir . $fileName) && $mode != MODE_EXPORT) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    File already exists: ' . $fileName);
        } else {
            $tpl = $this->helpers->getTpl('propertiesfile.php');
            $tpl = str_replace('[[+element]]',$objectName,$tpl);
            $tpl = str_replace('[[+elementType]]', substr(strtolower($this->dbClass), 3), $tpl);

            $tpl = $this->helpers->replaceTags($tpl);
            $hastags = strpos($tpl, '<'.'?'.'php');
            if ($hastags === false)
                $tpl = '<'.'?'.'php'.$tpl;
            $tpl .=  "\n\n" . $this->render_properties($properties) . "\n\n";

            if ($dryRun) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Would be creating: ' . $fileName . "\n");
                $this->modx->log(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
            }
            $this->helpers->writeFile($dir, $fileName, $tpl, $dryRun);
            if ($dryRun) {
                $this->modx->log(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
            }
            unset($tpl);
        }
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
}