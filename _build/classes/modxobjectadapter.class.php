abstract class MODxObjectAdapter
{       
    static protected $xPDOClass = '';
    static protected $xPDOClassIDKey = '';
    static protected $xPDOClassNameKey = '';
    static protected $xPDOTransportAttributes = '';
    
// MyComponent Object
    protected $myComponent;
// Database Columns/Properties
    protected $myColumns;
// Vehicle Resolution
    protected $myResolvers;
    protected $myValidators;
    protected $myFileTrees;
    
    protected __construct(&$forComponent)
    {//Set the component
        $this->myComponent =& $forComponent;
    }
    
    protected getXPDOClass()
    {//Simple Getter Function
        return static::xPDOClass; 
    }
    
    protected getAttributes()
    {//Simple Getter Function
        return static::xPDOTransportAttributes;
    }

    protected function toXPDOObject(&$modx);
    {//Use MODx to create the object
        $object = $modx->newObject($this->getXPDOClass);
        $object->fromArray($this->myColumns);
    // Return the XPDOObject
        return $object;
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

    protected function newTransport()
    {//Get the Build path
        $build = $myComponent->pathBuild;
        $data = $build . 'data/';
        $path = $data . strtolower(static::xPDOClass);
    // If data directory does not exist, create it
        if (!is_dir($data))
        {   if (mkdir($data, $this->dirPermission, true))
                $myComponent->log(MODX::LOG_LEVEL_INFO,'Created Directory: ' . $data);
            else
            {   $myComponent->log(MODX::LOG_LEVEL_INFO,'Could not create Directory: ' . $data);
                return false;
            }
        }
    // If Object directory does not exist, create it
        if (!is_dir($path))
        {   if (mkdir($path, $this->dirPermission, true))
                $myComponent->log(MODX::LOG_LEVEL_INFO,'Created Directory: ' . $path);
            else
            {   $myComponent->log(MODX::LOG_LEVEL_INFO,'Could not create Directory: ' . $path);
                return false;
            }
        }
    // Now that we know all directories exist...
    
    }
    
/* *****************************************************************************
   Import Objects and Support Functions 
***************************************************************************** */
    protected function addToMODx($overwrite = false)
    {//Quick Access
        $modx = $this->myComponent->modx;
    // MODx Class
        $objClass = static::xPDOClass;
    // Class ID Key, Name Key => Name Value Pair
        $idKey = static::xPDOClassIDKey;
        $nameKey = static::xPDOClassNameKey;
        $nameValue = $this->myColumns[$key]);
        
    // See if the object exists        
        $obj = $modx->getObject($objClass, array($nameKey => $nameValue));
    // Object exists/Cannot Overwrite
        if ($obj && !$overwrite) 
            $myComponent->log(MODX::LOG_LEVEL_INFO, $objClass . ' already exists: ' . $nameValue);
    // Object exists/Can Overwrite
        elseif ($obj && $overwrite)
        {//Avoid trouble
            unset($this->myColumns[$idKey]);
            if ($idKey != $nameKey)
                unset($this->myColumns[$nameKey]);
        // Set all Columns
            $obj->fromArray($this->myColumns);
        // Realign (just in case)
            this->myColumns[$nameKey] = $nameValue;
        // Save Object
            if ($obj->save())
            // Report success
                $myComponent->log(MODX::LOG_LEVEL_INFO, 'Updated '. $objClass .': ' . $nameValue);
            else
            // Report failure
                return -1;
        }
    // Object does not exist
        elseif (!$obj)
        {//Avoid trouble
            if ($idKey != $nameKey)
                unset($this->myColumns[$idKey]);
        //Create the new MODx Object
            $obj = $modx->newObject($objClass, array($nameKey => $nameValue));
            $obj->fromArray($this->myColumns);
            if ($obj->save())
            // Report success
                $myComponent->log(MODX::LOG_LEVEL_INFO, 'Created '. $objClass .': ' . $nameValue);
            else
            // Report failure
                return -1;
        }
    // Return the ID of the object
        return $obj->get($idKey);
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
    public function exportObject($element)
    {
        if (stristr($element,'menus')) { /* note: may change in Revo 2.3 */
            $element='Actions';
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, "\n\nProcessing " . $element);
        
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Category: ' . $this->category);
        /* convert 'chunks' to 'modChunk' etc. */
        $this->elementType = 'mod' . substr(ucFirst($element),0,-1);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Element Type: ' . $this->elementType);
        
        /* use namespace rather than category for these */
        $key = $this->elementType == 'modSystemSetting' ||  $this->elementType =='modAction' ? 'namespace' : 'category';
        /* category ID or category name, depending on what we're looking for */
        $value = $this->elementType =='modAction'  ? strtolower($this->category) : $this->categoryId;
        /* get the objects */
        $this->elements = $this->modx->getCollection($this->elementType, array($key => $value));

        /* try again with actual category name (camel case) */
        if (empty($this->elements) && ($this->elementType == 'modSystemSetting' || $this->elementType == 'modSystemEvent' || $this->elementType == 'modAction')) {

            $value = $this->category;
            $this->elements = $this->modx->getCollection($this->elementType, array($key => $value));
        }

        if (empty($this->elements)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'No objects found in category: ' . $this->category);
            return;
        }


        $transportFile = 'transport.' . strtolower($element) . '.php';
        $transportFile = str_replace('templatevars', 'tvs', $transportFile);
        $transportFile = str_replace('systemsettings', 'settings', $transportFile);
        $transportDir = $this->transportPath;


        /* write transport header */
        $tpl = $this->helpers->getTpl('transportfile.php');
        $tpl = str_replace('[[+elementType]]', $element, $tpl);
        $tpl = $this->helpers->replaceTags($tpl);

        $tpl .= "\n\$" . strtolower($element) . " = array();\n\n";

        $i=1;
        /* append the code (returned from writeObject) for each object to $tpl */
        foreach($this->elements as $elementObj) {
            $tpl .= $this->exportColumns($elementObj, strtolower(substr($element, 0, -1)), $i);
            $i++;
        }
        /* write transport footer */
        $tpl .= 'return $' . strtolower($element) . ";\n";

        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '    Would be creating: ' . $transportFile . "\n");
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
        }
        $this->helpers->writeFile($transportDir, $transportFile, $tpl, $this->dryRun);
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
        }
        unset($tpl);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Finished processing: ' . $element);
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
    protected function exportColumns($elementObj, $element, $i) {
        /* element is in the form 'chunk', 'snippet', etc. */
        /* @var $elementObj modElement */

        /* write generic stuff */
        $tpl = '$' . $element . 's[' . $i . '] = $modx->newObject(' . "'" . $this->elementType . "');" . "\n";
        $tpl .= '$' . $element . 's[' . $i . '] ->fromArray(array(' . "\n";
        $tpl .= "    'id' => " . $i . ",\n";

        $fields = $elementObj->toArray('', true);  // true gets raw values - check this

        /* This may not be necessary */
        /* *********** */
        $properties = $elementObj->get('properties');
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

        $name = $elementObj->get($this->helpers->getNameAlias($this->elementType));
        $type = $this->elementType;
        $fileName = $this->helpers->getFileName($name, $type);
        switch ($this->elementType) {

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

        if ($this->elementType == 'modResource') {
            $tpl .= "\$resources[" . $i . "]->setContent(file_get_contents(\$sources['data']." . "'resources/" . $fileName . "'));\n\n";
        }

        /* handle properties */
        if ($hasProperties) {
            $name = $elementObj->get($this->helpers->getNameAlias($this->elementType));
            $fileName = $this->helpers->getFileName($name, $this->elementType, 'properties');
            $tpl .= "\n\$properties = include \$sources['data'].'properties/" . $fileName ."';\n" ;
            $tpl .= '$' . $element . "s[" . $i . "]->setProperties(\$properties);\n";
            $tpl .= "unset(\$properties);\n\n";
            $this->writePropertyFile($properties, $fileName, $name);
        }
        return $tpl;
    }

/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */
    protected function buildVehicle()
    {//Quick Access
        $modx = $this->myComponent->modx;
        $builder = $this->myComponent->builder;
        $validate = $this->myValidators;
        $resolve = $this->myResolvers;
    // We must have MODx Object
        if (empty($modx) || empty($builder))
            return false;
    // Make sure we have column values to export
        if (empty($this->myColumns)
        ||  !is_array($this->myColumns))
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Vehicle has no database values');
            return false;
        }
    // We must have Attributes in order to Package
        $attr = $this->getAttributes();
        if (empty($attr)
        ||  is_array($attr))
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Could not package Vehicle: ' . this->getXPDOClass);
            return false;
        }
        else
        {//Update for Validators
            if (is_array($this->myValidators)) 
                $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
        // Update for 
        }
        

        }
    // We must have a valid xPDO Object to Package
        $obj = $this->toXPDOObject($modx)
        if (empty($obj))
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Could not create xPDO object: ' . this->getXPDOClass);
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
}