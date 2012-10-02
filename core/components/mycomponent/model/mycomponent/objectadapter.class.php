<?php
abstract class ObjectAdapter
{//Base required     
    static protected $dbClass;
    static protected $dbClassIDKey;
    static protected $dbClassNameKey;
    static protected $dbClassParentKey;
    // protected $dbTransportAttributes = '';
    /* @var $modx modX */
    public $modx;
    
// MyComponent Object
    protected $myComponent;
// Database Columns/Properties
    protected $myFields;
// Vehicle Resolution
    protected $myResolvers;
    protected $myValidators;
    protected $myFileTrees;
    
    public function __construct(&$forComponent)
    {//Set the component
        $this->myComponent =& $forComponent;
    }
    
/* *****************************************************************************
   Property Getter and Setters
***************************************************************************** */

    /**
     * Convenience Method for getting the name of the 'name' field for the object.
     * We use this instead of a switch/case, because its faster and more easily
     * maintained. If a change occurs, we don't have to add a new case.
     *
     * @return string - The name of the 'name' field.
     */
    public function getNameField() 
    {//Simple Getter Function
        return static::$dbClassNameKey;
    }

    /**
     * Convenience Method for getting the Name of the current object. This serves
     * the purpose of accessing the $name variable.
     * 
     * @return String - The unmodified Name.
     */
    public function getName()
    {//Simple Getter Function
        return $this->myFields[static::$dbClassNameKey];
    }
    
    /**
     * Convenience Method for getting the File System Safe Name of the current
     * object. This serves the purpose of accessing the $name variable.
     *
     * @return string - modified lowercase Name with no spaces.
     */
    public function getSafeName()
    {//Simple Getter Function
        $name = static::$dbClassNameKey;
        $name = strtolower($name);
        $name = $output = str_replace(' ', '', $name);
    // Return the new name
        return $name;
    }
    
    /**
     * Convenience Method for getting the xPDO Class of the current object.
     * This serves the purpose of accessing the $class variable.
     * 
     * @return String - The proper 'mod' prefixed class for MODx.
     */
    public function getClass()
    {//Simple Getter Function
        return static::$dbClass;
    }
    
    /**
     * Convenience Method for getting the File System Safe xPDO Class of the 
     * current object. This serves the purpose of accessing the $class variable.
     *
     * @return string - The lowercase class without the 'mod' prefix.
     */
    public function getSafeClass()
    {//Simple Getter Function
        $class = substr(strtolower(static::$dbClass), 3);
        if ($class == 'templatevar')
            return 'tv';
        elseif ($class == 'systemsettings')
            return 'setting';
        else
            return $class;
    }
    
    public function getAttributes()
    {//Simple Getter Function
        // return static::dbTransportAttributes;
    }

    /**
     * Returns the correct filename for a given file
     *
     * @param string $fileType string - Type of file to be created (code, properties, transport)
     * @return string
     *
     * Example returns for MyObject plugin-type object
     *    code:  myobject.plugin.php
     *    transport: transport.plugin.myobject.php
     *    properties: properties.myobject.plugin.php
     */
    public function getFileName($fileType = 'code') 
    {//Forward the Calls
        if ($fileType == 'transport') 
            return $this->getTransportFileName();
        elseif ($fileType == 'code') 
            return $this->getCodeFileName();
        elseif ($fileType == 'properties')
            return $this->getPropertiesFileName();
        else
            return '';
    }

    public function getCodeFileName()
    {//For Quick Access
        $type = $this->getClass();
        $name = $this->getSafeName();
        $suffix = $this->getSafeClass();
        
    // Initialize Defaults
        $output = '';
        $extension = 'php';
            
    // Run the list...
        switch ($type) 
        {   case 'modResource':
                $suffix = 'content';
            case 'modTemplate':
            case 'modChunk':
                $extension = 'html';
            case 'modSnippet':
            case 'modPlugin':
                $output = $name .'.'. $suffix . '.' . $extension;
                break;
            default:  /* all other elements get no code file */
                $output = '';
                break;
        }
        return $output;
    }
    public function getTransportFileName()
    {//Simple Calculation
        return 'transport.' . $this->getSafeClass() . '.' . $this->getSafeName() . '.php';
    }
    public function getPropertiesFileName()
    {//Simple Calculation
        return 'properties.' . $this->getSafeName() . '.' . $this->getSafeClass() . '.php';
    }
    
    public function setResolvers($resolvers)
    {//We accept an array or a comma-delimited list.
        if (is_array($resolvers))
            $this->myResolvers = $resolvers;
        else
            $this->myResolvers = explode(',', $resolvers);
    }
    
    public function setValidators($validators)
    {//We accept an array or a comma-delimited list.
        if (is_array($validators))
            $this->myValidators = $resolvers;
        else
            $this->myValidators = explode(',', $validators);
    }
    
    public function setFileTrees($directories)
    {//We accept an array or a comma-delimited list.
        if (is_array($directories))
            $this->myFileTrees = $directories;
        else
            $this->myFileTrees = explode(',', $directories);
    }

/* *****************************************************************************
   Bootstrap and Support Functions 
***************************************************************************** */

    public function newTransport()
    {//Get the Build path
        $mc = $this->myComponent;
        $data = $mc->getPath('data');
        $path = $data . $this->getSafeClass() . '/';
    // If data directory does not exist, create it
        if (!$mc->makeDir($data, false))
        {   $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Could not create Transport: Data directory was not created!');
            return false;
        }
    // If Object directory does not exist, create it
        if (!$mc->makeDir($path, false))
        {   $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Could not create Transport: Object directory was not created!');
            return false;
        }
    // Now that we know all directories exist...
        $filename = $this->getTransportFileName();
        //$mc->
        
    // Do some post-processing
        $this->createResolvers();
    }
    
    /** Creates additional resolvers specified in project config file */
    public function createResolvers() 
    {//For Quick Access
        $mc = $this->myComponent;
        $modx = $mc->modx;
        $path = $mc->getPath('resolve');

/*            if (!is_dir($dir)) {
                mkdir($dir, $this->dirPermission, true);
            }
*/
        //$resolvers = $modx->getOption('resolvers', $this->props, '');
        $resolvers = $this->myResolvers;
    
        if (!empty($resolvers)) 
        {   $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating extra resolvers');
            foreach ($resolvers as $resolver) 
            {
                if ($resolver == 'default') 
                    $fileName = $this->packageNameLower . '.resolver.php';
                elseif (!empty($resolver))
                    $fileName = $resolver . '.resolver.php';
                
                if (file_exists($path . $fileName))
                    $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                else 
                {
                    $tpl = $this->getTpl('genericresolver.php');
                    $tpl = $mc->replaceTags($tpl);
                    $mc->writeFile($dir, $fileName, $tpl);
                }
            }
        }
    }
    
/* *****************************************************************************
   Import Objects and Support Functions 
***************************************************************************** */
    public function addToMODx($overwrite = false)
    {//Quick Access
        /* @var $modx modX */
        $mc = $this->myComponent;
        $modx =& $mc->modx;
    // MODx Class
        $objClass = $this->getClass();
    // Class ID Key, Name Key => Name Value Pair
        $idKey = static::$dbClassIDKey;
        $name = $this->getName();
        $nameKey = $this->getNameField();
        
    // See if the object exists        
        $obj = $modx->getObject($objClass, array($nameKey => $name));
    // Object exists/Cannot Overwrite
        if ($obj && !$overwrite) 
        {   $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $objClass . ' already exists: ' . $name);
            return -1;
        }
    // Object exists/Can Overwrite
        elseif ($obj && $overwrite)
        {//Avoid trouble
            unset($this->myFields[$idKey]);
            if ($idKey != $nameKey)
                unset($this->myFields[$nameKey]);
        // Set all Columns
            $obj->fromArray($this->myFields, '', true, true);
        // Realign (just in case)
            $this->myFields[$nameKey] = $name;
        // Save Object
            if ($obj->save()) {
            // Report success
                $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Updated '. $objClass .': ' . $name);
            } else
            // Report failure
                return -1;
        }
    // Object does not exist
        elseif (!$obj)
        {//Avoid trouble
            if ($idKey != $nameKey)
                unset($this->myFields[$idKey]);
        //Create the new MODx Object
            $obj = $modx->newObject($objClass, array($nameKey => $name));
            if ($obj && $obj instanceof $objClass) {
                $obj->fromArray($this->myFields, '', true, true);
                // die(print_r($this->myFields, true));
            } else {
                $msg = "\nFailed to create object \nclass: " . $objClass . "\nnameKey: " . $nameKey . "\nname: " . $name;
                $mc->helpers->sendLog(MODX::LOG_LEVEL_ERROR,$msg);
                die();
            }
            if ($obj->save())
            // Report success
                $mc->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Created '. $objClass .': ' . $name);
            } else {
            // Report failure
                return -1;
            }
    // Return the ID of the object
        // return $obj->get($idKey);
        return $obj;
    }
    
    public function toDBObject()
    {///Use MODx to create the object
        $object = $this->myComponent->modx->newObject($this->getClass());
        $object->fromArray($this->myFields, '', true, true);
    // Return the XPDOObject
        return $object;
    }

/* *****************************************************************************
   Export Objects and Support Functions 
***************************************************************************** */
    /**
     * Processes all elements of specified type that are in the category or area
     * (resources are specified by parent and/or list of pagetitles).
     *
     * (optionally) writes code file and transport file
     *
     * @param $element - string element type('snippets', 'plugins' etc.)
     */
    public function exportObject($element, $overwrite = false)
    {//For Quick Access
        $mc = $this->myComponent;
        $name = $this->getName();
        $type = $this->getClass();
        $safetype = $this->getSafeClass();
    // For writing the Transport File
        $path = $mc->getPath('data');

        if (stristr($element,'menus')) { /* note: may change in Revo 2.3 */
            $element='Actions';
        }

        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n\nProcessing " . $safetype . ': ' . $name);
        
        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Category: ' . $this->category);
        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Element Type: ' . $type);
        
        /* use namespace rather than category for these */
        $key = $type == 'modSystemSetting' ||  $type =='modAction' 
            ? 'namespace' 
            : 'category';
        /* category ID or category name, depending on what we're looking for */
        $value = $type =='modAction'  
            ? strtolower($this->category) 
            : $this->categoryId;
        /* get the objects */
        $this->elements = $this->modx->getCollection($type, array($key => $value));

        /* try again with actual category name (camel case) */
        if (empty($this->elements) 
        &&  ($type == 'modSystemSetting' 
            || $type == 'modSystemEvent' 
            || $type == 'modAction')) 
        {
            $value = $this->category;
            $this->elements = $this->modx->getCollection($type, array($key => $value));
        }

        if (empty($this->elements)) {
            $mc->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'No objects found in category: ' . $this->category);
            return;
        }
        
    // Get the Transport File Name
        $transportFile = getFileName('tranport');

        /* write transport header */
        $tpl = $this->getTpl('transportfile.php');
        $tpl = str_replace('[[+elementType]]', $element, $tpl);
        $tpl = $mc->replaceTags($tpl);

        $tpl .= "\n\$" . strtolower($element) . " = array();\n\n";

        $i=1;
        /* append the code (returned from writeObject) for each object to $tpl */
        foreach($this->elements as $elementObj) {
            $tpl .= $this->exportColumns($elementObj, strtolower(substr($element, 0, -1)), $i);
            $i++;
        }
        /* write transport footer */
        $tpl .= 'return $' . strtolower($element) . ";\n";

        $mc->writeFile($path, $transportFile, $tpl, $this->dryRun);
        $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Finished processing: ' . $element);
        
        unset($tpl);
    }
    
    /**
     * Creates code for an individual element to be written to transport file
     * and properties file for any objects with properties
     *
     * @param $elementObj - MODX object (the element)
     * @param $element - type of object ('plugin', 'snippet', etc.)
     * @param $i int - index of element in transport file
     * @return string - code for this object to be inserted in transport file (by $this->process())
     */
    public function exportColumns($elementObj, $element, $i) 
    {//For Quick Access
        $type = $this->getClass();
        $safeType = $this->getSafeClass();
        $fields = $this->myColumns;
        
        /* element is in the form 'chunk', 'snippet', etc. */
        /* @var $elementObj modElement */

        /* write generic stuff */
        $tpl = '$' . $safeType . 's[' . $i . '] = $modx->newObject(' . "'" . $type . "');" . "\n";
        $tpl .= '$' . $safeType . 's[' . $i . '] ->fromArray(array(' . "\n";
        $tpl .= "    'id' => " . $i . ",\n";


        /* This may not be necessary */
        /* *********** */
        $properties = $fields['properties'];
        $hasProperties = false;
        if (!empty($properties)) {
            /* handled below */
            $hasProperties = true;
            unset($fields['properties']);
        } else {
            ($fields['properties'] ='');
        }
        /* ************  */
        unset($fields['id'],
            $fields['snippet'],
            $fields['content'],
            $fields['plugincode'],
            $fields['editor_type'],
            $fields['category'],
            $fields['static'],
            $fields['static_file'],
            $fields['moduleguid'],
            $fields['locked'],
            $fields['source'],
            $fields['cache_type'],
            $fields['parent'],
            $fields['pub_date'],
            $fields['unpub_date'],
            $fields['createdon'],
            $fields['publishedon'],
            $fields['publishedby'],
            $fields['uri'],
            $fields['uri_override'],
            $fields['editedon'],
            $fields['desc_trans'],
            $fields['text'],
            $fields['menu']
        );

        foreach ($fields as $field => $value) {
            if ($field == 'value'  && in_array('combo-boolean', array_values($fields))) {
                $value = $value? 'true' : 'false';
                $tpl .= "    '" . $field . "'" . " => " . $value . ",\n";
            } else {
                $tpl .= "    '" . $field . "'" . " => '" . $value . "',\n";
            }
        }
        /* ToDo: Property Sets */
        /* write object-specific stuff */

        $name = $elementObj->get($this->getNameField());
        $fileName = $this->getFileName();
        switch ($type) {

            case 'modChunk':
                $tpl .= "    'snippet' => file_get_contents(\$sources['source_core']." . "'/elements/chunks/" . $fileName . "'),\n";
                break;

            case 'modSnippet':
                $tpl .= "    'snippet' => stripPhpTags(\$sources['source_core']." . "'/elements/snippets/" . $fileName . "'),\n";
                break;

            case 'modPlugin':
                $tpl .= "    'plugincode' => stripPhpTags(\$sources['source_core']." . "'/elements/plugins/" . $fileName . "'),\n";
                break;

            case 'modTemplate':
                $tpl .= "    'content' => file_get_contents(\$sources['source_core']." . "'/elements/templates/" . $fileName . "'),\n";
                break;

            default:
                break;
        }
        /* finish up */
        $tpl .= "), '', true, true);\n";

        if ($class == 'modResource') {
            $tpl .= "\$resources[" . $i . "]->setContent(file_get_contents(\$sources['data']." . "'resources/" . $fileName . "'));\n\n";
        }

        /* handle properties */
        if ($hasProperties) {
            $name = $elementObj->get($this->getNameField());
            $fileName = $this->getFileName('properties');
            $tpl .= "\n\$properties = include \$sources['data'].'properties/" . $fileName ."';\n" ;
            $tpl .= '$' . $element . "s[" . $i . "]->setProperties(\$properties);\n";
            $tpl .= "unset(\$properties);\n\n";
            $this->writePropertyFile($properties, $fileName, $name);
        }
        return $tpl;
    }

    /**
     * Creates the code file for an element or resource - skips static elements
     *
     * @param $elementObj modElement - element MODX object
     * @param $element - string name of element type ('plugin', 'snippet' etc.) used in dir name.
     */
    public function exportCode ($elementObj, $element) 
    {//For Quick Access
        /* @var $mc MyComponentProject */
        $mc = $this->myComponent;
        $name = $this->getName();
        $class = $this->getSafeClass();
        
        /* @var $elementObj modElement */

        if ($elementObj->get('static')) {
            $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Skipping object file for static object: ' . $name);
            return;
        }
        $type = $this->elementType;
        $name = $elementObj->get($this->getNameField());

        $fileName = $this->getFileName('code');
        if ($fileName) {
            $content = $elementObj->getContent();
        } else {
            $mc->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Skipping object file for: ' . $type . '; object (does not need source file)');
            return;
        }
        if ($type == 'modResource') {
            $dir = $this->resourcePath;
        } else {
            $dir = $this->getCodeDir($this->targetCore, $type);
        }

         $tpl = '';
        if ($type == 'modSnippet' || $type == 'modPlugin') {
            if (! strstr($content, '')) {
                $tpl .= '<' . '?' . 'php' . "\n\n";

            }

            if ( (!strstr($content,'GNU')) && (!stristr($content,'License')) ) {
                $tpl = $this->getTpl('phpfile.php');
                $tpl = str_replace('[[+elementName]]', $name, $tpl);
                $tpl = str_replace('[[+elementType]]', $class, $tpl);
                $tpl = $mc->helpers->replaceTags($tpl);
            }
        }
        $tpl .= $content;

        $mc->writeFile($dir, $fileName, $tpl, $isDry);
        unset($tpl);
    }

/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */
    public function buildVehicle()
    {//Quick Access
        $mc = $this->myComponent;
        $modx = $mc->modx;
        $builder = $mc->builder;
        $validate = $this->myValidators;
        $resolve = $this->myResolvers;
    // We must have MODx Object
        if (empty($modx) || empty($builder))
            return false;
    // Make sure we have column values to export
        if (empty($this->myFields)
        ||  !is_array($this->myFields))
        {   $mc->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Vehicle has no database values');
            return false;
        }
    // We must have Attributes in order to Package
        $attr = $this->getAttributes();
        if (empty($attr)
        ||  is_array($attr))
        {   $mc->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Could not package Vehicle: ' . $this->getClass());
            return false;
        }
        else
        {//Update for Validators
            // if (is_array($this->myValidators))
               // $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
        // Update for 
        }
        
    // We must have a valid xPDO Object to Package
        $obj = $this->toDBObject($modx);
        if (empty($obj))
        {   $mc->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Could not create xPDO object: ' . $this->getDBClass());
            return false;
        }
    // Create the Vehicle
        $new = $builder->createVehicle($obj, $attr);
    // Add all of the Validators
        if (!empty($validate))
            foreach($validate as $validator)
                if (!empty($validator))
                {   $file = $sources['validators'] . $validator . '.validator.php';
                    if (file_exists($file))
                        $new->validate('php',array('source' => $file,));;
                }
    // Add all of the Resolvers
        if (!empty($resolve))
            foreach($resolve as $resolver)
                if (!empty($resolver))
                {   $file = $sources['resolvers'] . $resolver . '.resolver.php';
                    if (file_exists($file))
                        $new->resolve('php',array('source' => $file,));;
                }
        $builder->putVehicle($new);
    }
    
/* *****************************************************************************
   General Support Functions 
***************************************************************************** */
    /**
     * Get tpl file contents from the installed MODx Chunks or from the MC build 
     * tpl directory. Chunks and files may be prefixed with 'safepackagename.' or
     * 'my.'. Will automatically get 'packagename.', then 'my.', then default 
     * (no prefix).
     *
     * @param $name string  - Name of tpl file
     * @return string - Content of tpl file or '' if it doesn't exist
     */
    public function getTpl($name)
    {//Initialize
        $text = '';
        $name = strtolower($name);
    // For Quick Access
        $mc = $this->myComponent;
        $path = $mc->getPath('mcTpl');
        $modx = $mc->modx;
        
    /* Check MODx Chunks first */
    // Check for Package Specific Chunks
        $prefix = $mc->getSafeName();
        $tpl = $modx->getObject('modChunk', array('name' => $prefix . '.' . $name));
        if (!empty($tpl))
            return $tpl->get('snippet');
    // Check for User Specified Chunks
        $prefix = 'my.';
        $tpl = $modx->getObject('modChunk', array('name' => $prefix . '.' . $name));
        if (!empty($tpl))
            return $tpl->get('snippet');
    // Check for Default Chunks
        $tpl = $modx->getObject('modChunk', array('name' => $name));
        if (!empty($tpl))
            return $tpl->get('snippet');
        
    /* Resort to the File System */
        if (strstr($name, '.php')) { /* already has extension */
            $text = @file_get_contents($path . 'my' . $name);
            if (empty($text)) {
                $text = @file_get_contents($path . $name);
            }
        } else { /* use .tpl extension */
            $text = @file_get_contents($path . 'my' .  $name . '.tpl');
            if (empty($text)) {
                $text = @file_get_contents($path . $name . '.tpl');
            }
        }
        return $text !== false ? $text : '';
    }
}