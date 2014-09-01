<?php

if (false === function_exists('lcfirst')):
    function lcfirst($str) {
        return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
    }
endif;


abstract class ObjectAdapter {
    /**
     * @var string - modResource, modChunk, etc.
     */
    protected $dbClass = '';
    /**
     * @var $dbClassIDKeu string - Primary key field name
     */
    protected $dbClassIDKey = 'id';

    /**
     * @var $dbClassNameKey string - pagetitle, templatename, name, etc.
     */
    protected $dbClassNameKey = '';

    /**
     * @var $dbClassParentKey string - name of parent field (parent, category, etc)
     */
    protected $dbClassParentKey = '';

    /* @var $helpers Helpers - helpers class */
    public $helpers;

    /* @var $modx modX */
    public $modx;

    /**
     * @var $name string - object name
     */
    protected $name = '';

    /**
     * @var $createProcessor string - path of object's create processor
     */
    protected $createProcessor = '';

    /**
     * @var $updateProcessor string - path of object's update processor
     */
    protected $updateProcessor = '';

    /**
     * @var $myId integer - Primary key value for current object
     */
    protected $myId;

    /**
     * @var $myFields array - associative array of field names/values for current object
     */
    protected $myFields;

    /**
     * @var $myObjects static array - Master array of all objects being processed
     * by MyComponent
     */
    public static $myObjects = array();


    /**
     * @param $modx modX
     * @param $helpers Helpers
     */
    public function __construct(&$modx, &$helpers) {
        $this->modx =& $modx;
        $this->helpers =& $helpers;
    }

    /**
     * Return master array of all objects being processed by MyComponent
     *
     * @return array|static
     */
    public static function getObjects() {
        return self::$myObjects;
    }

    /**
     * Get Name of current object (e.g., value of name, pagetitle, category, etc.
     *
     * @return string
     */
    public function getName() {
        return ($this->name);
    }

    /**
     * Return path to update or create processor
     *
     * @param $mode
     * @return string
     */
    public function getProcessor($mode) {
        return $mode == 'create'
            ? $this->createProcessor
            : $this->updateProcessor;

    }

    /**
     * Convenience Method for getting the name of the 'name' field for an object.
     *
     * @return string - The name of the 'name' field.
     */
    public function getNameField() {
        return $this->dbClassNameKey;
    }


    /**
     * Convenience Method for getting the File System Safe Name of the current
     * object.
     *
     * @return string - modified lowercase Name with no spaces.
     */
    public function getSafeName() {
        $name = $this->dbClassNameKey;
        $name = strtolower(str_replace(' ', '', $name));
        return $name;
    }
    
    /**
     * Convenience Method for getting the xPDO Class of the current object.
     *
     * @return String - The proper 'mod' prefixed class for MODx.
     */
    public function getClass() {
        return $this->dbClass;
    }
    
    /**
     * Convenience Method for getting the File System Safe xPDO Class of the 
     * current object.
     *
     * This is for use in file names (e.g.'snippet' for use in snippet1.snippet.php)
     *
     * @return string - The lowercase class without the 'mod' prefix.
     */
    public function getSafeClass() {
        $class = substr(strtolower($this->dbClass), 3);
        if ($class == 'templatevar')
            return 'tv';
        elseif ($class == 'systemsettings')
            return 'setting';
        else
            return $class;
    }


    /**
     * Returns the correct filename for a given file
     *
     * @param string $fileType string - Type of file to be created (code, properties, transport)
     * @return string
     *
     * Example returns for MyObject plugin-type object
     *    code:  plugin1.plugin.php
     *    transport: transport.plugin.myobject.php
     *    properties: properties.myobject.plugin.php
     */
    public function getFileName($fileType = 'code') {
        if ($fileType == 'transport') 
            return $this->getTransportFileName();
        elseif ($fileType == 'code') 
            return $this->getCodeFileName();
        elseif ($fileType == 'properties')
            return $this->getPropertiesFileName();
        else
            return '';
    }


    /**
     * @return string
     */
    public function getCodeFileName() {//For Quick Access
        $type = $this->getClass();
        $name = $this->getSafeName();
        $suffix = $this->getSafeClass();
        
    /* Initialize Defaults */
        $output = '';
        $extension = 'php';
            
    /* fall-throughs are intentional */
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
            default:  /* all other elements need no code file */
                $output = '';
                break;
        }
        return $output;
    }

    /**
     * Get the name of the transport file for the current object
     * @return string
     */
    public function getTransportFileName() {
        return 'transport.' . $this->getSafeClass() . '.' . $this->getSafeName() . '.php';
    }

    /**
     * Get the name of the properties file for the current object
     *
     * @return string
     */
    public function getPropertiesFileName() {
        return 'properties.' . $this->getSafeName() . '.' . $this->getSafeClass() . '.php';
    }


    /**
     * Add a new object to MODX
     *
     * @param bool $overwrite - if set allows overwriting existing objects
     * @return bool|int - true=success, false=failure, -1=object already exists & !overwrite
     */
    public function addToMODx($overwrite = false) {
        /* @var $modx modX */
        $modx =& $this->modx;
        $objClass = $this->getClass();
        /* Class ID Key, Name Key => Name Value Pair */
        $idKey = $this->dbClassIDKey;
        $name = $this->getName();
        $nameKey = $this->getNameField();
        $id = null;


       /* See if the object exists */
        $obj = $modx->getObject($objClass, array($nameKey => $name));
        if ($obj) {
            $id = $obj->get($idKey);
        }
        /* Object exists/Cannot Overwrite */
        if ($obj && !$overwrite) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $objClass . ' ' .
                $this->modx->lexicon('mc_already_exists')
                .': ' . $name);
            $id = $obj->get($idKey);

            if (isset($this->myFields['category'])) {
                $oldCat = $obj->get('category');
                if ($oldCat && $oldCat != $this->myFields['category']) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                        $this->modx->lexicon('mc_outdated_category')
                            . ' ' . $this->myFields['category']);
                    if (is_numeric($this->myFields['category'])) {
                        $obj->set('category', $this->myFields['category']);
                        $obj->save();
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_updated_category_for')
                                . ': ' . $name);
                    }
                }
            }
        /* Object exists/Can Overwrite */
        } elseif ($obj && $overwrite) {
            unset($this->myFields[$idKey]);
            if ($idKey != $nameKey) {
                unset($this->myFields[$nameKey]);
            }

            $processor = $this->getProcessor('update');
            /* @var $response modProcessorResponse */
            $response = $modx->runProcessor($processor, $this->myFields);
            if (empty($response) || $response->isError()) {
                $msg = "[Object Adapter] " .
                    $this->modx->lexicon('mc_failed_to_create_object')
                     . "\n    class: " . $objClass .
                    "\n    nameKey: " . $nameKey . "\n    name: " . $name;
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, $msg);
                $id =  -1;
            } else {
                /* @var $obj xPDOObject */
                $obj = $response->getObject();
                $id = $obj->get($idKey);

                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created')
                        . ' ' . $objClass . ': ' . $name);
            }

        /* Object does not exist - create it */
        } elseif (!$obj) {
            if ($idKey != $nameKey) {
                unset($this->myFields[$idKey]);
            }
            if ($this->dbClass == 'modResource' && isset($this->myFields['tvValues'])) {
                $tvValues = $this->myFields['tvValues'];
                unset($this->myFields['tvValues']);
            }
            /* sets appropriate content field for elements and resources */

            $this->setContentField($name, $this->dbClass);

            if (isset($this->myFields['filename'])) {
                $tempFilename = $this->myFields['filename'];
                unset($this->myFields['filename']);
            }
            $processor = $this->getProcessor('create');
            $response = $modx->runProcessor($processor, $this->myFields);
            if (!empty($tempFilename)) {
                $this->myFields['filename'] = $tempFilename;
                unset($tempFilename);
            }
            if (empty($response) || $response->isError()) {
                $msg = "[Object Adapter] " .
                    $this->modx->lexicon('mc_failed_to_create_object')
                    . "\n    class: " . $objClass .
                    "\n    nameKey: " . $nameKey . "\n    name: " . $name;
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, $msg);
                $id = false;
            } else {
                /* @var $o xPDOObject */
                if (is_object($response)) {
                    $o = $response->getObject();
                    if (is_array($o) && isset($o[$this->dbClassIDKey])) {
                        $id = $o[$this->dbClassIDKey];

                        if ($this->dbClass == 'modResource' && isset($tvValues)) {
                            $resource = $this->modx->getObject('modResource', $id);
                            if ($resource) {
                                foreach($tvValues as $k => $v) {
                                    $resource->setTVValue($k, $v);
                                }
                                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                                    $this->modx->lexicon('mc_set_tv_values')
                                    . ' ' . $this->myFields['pagetitle']);
                                unset($resource);
                            }
                        }
                    }

                }

                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created')
                        . ' '. $objClass . ': ' . $name);
            }
        }
        if (! $id) {
           /* $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[Object Adapter] ' .
                $this->modx->lexicon('mc_no_id_for')
                    . ' ' . $objClass . ' ' . $name);*/
        } else {
            $this->myId = $id;
        }
    }


    /**
     * Remove object from MODX DB
     */
    public function remove() {
        $modx =& $this->modx;
        $objClass = $this->getClass();
        /* Class ID Key, Name Key => Name Value Pair */
        $idKey = $this->dbClassIDKey;
        $name = $this->getName();
        $nameKey = $this->getNameField();
        $id = null;


        /* See if the object exists */
        $obj = $modx->getObject($objClass, array($nameKey => $name));
        if ($obj) {
            $obj->remove();
            $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $this->modx->lexicon('mc_removed')
                    . ' '. $objClass . ': ' . $name);
            $this->modx->setLogLevel($temp);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                '[Object Adapter] ' .
                    $this->modx->lexicon('mc_class_file_nf')
                        . ' ' . $objClass . ': ' . $name);
        }
    }

    /**
     *  Set the content field for resources and elements
     *  during Bootstrap
     *  @param $name string - $name of the object
     *  @param $type string - type (e.g., 'modSnippet');
     * @return string = tpl code
     */
    public function setContentField($name, $type ) {
        $name = strtolower($name);
        $tpl = '';
        $field = '';
        if ($type == 'modResource') {
            $x = 1;
        }
        $suppliedFileName = null;
        if (isset($this->myFields['filename'])) {
            $suppliedFileName = $this->myFields['filename'];
        }
        if ($type == 'modResource') {
            $dir = $this->helpers->props['targetRoot'];
        } else {
            $dir = $this->helpers->props['targetRoot'] . 'core/components/';
            $dir .= $this->helpers->props['packageNameLower'] . '/';
        }
        $codeDir = $this->helpers->getCodeDir($dir, $type);
        $path = $codeDir . '/' . $suppliedFileName;
        if ($suppliedFileName && file_exists($path)) {
            $tpl = file_get_contents($path);
        } else {
            $fileName = $this->helpers->getFileName($name, $type);
            /* Use file content if file exists */
            $path = $codeDir . '/' . $fileName;
            if (file_exists ($path)) {
                $tpl = file_get_contents($codeDir . '/' . $fileName);
            }
        }
        if (empty($tpl)) {
            /* no file, use Tpl chunk */
            $tplName = strtolower($this->dbClass);
            if ($tplName == 'modplugin' || $tplName == 'modsnippet') {
                $tplName = 'phpfile.php';
            }
            $tpl = $this->helpers->getTpl($tplName);
        }
        if (!empty($tpl)) {
            switch($this->dbClass) {
                case 'modChunk':
                case 'modSnippet':
                    $field = 'snippet';
                    break;
                case 'modPlugin':
                    $field = 'plugincode';
                    break;
                case 'modResource':
                case 'modTemplate':
                    $field = 'content';
                    break;
                default:
                    break;
            }
            $tpl = str_replace('[[+elementType]]', strtolower(substr($this->dbClass, 3)), $tpl);
            $tpl = str_replace('[[+elementName]]', $this->getName(), $tpl);
            if (isset($this->myFields['description'])) {
                $description = $this->myFields['description'];
                if (strstr($description, '~~')) {
                    $split = explode('~~', $description);
                    $description = $split[1];
                }
                $tpl = str_replace('[[+description]]', $description, $tpl);
            }
            if (!empty ($tpl)) {
                $tpl = $this->helpers->replaceTags($tpl);
            }
            if (!empty($tpl) && !empty($field)) {
                $this->myFields[$field] = $tpl;
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_tpl_nf')
                    . ': ' . $name);
            }
        }
        return $tpl;
    }

    /**
     * Create the code file for the current object. Overwrites on Export,
     * not on Bootstrap
     *
     * @param bool $overwrite - if set, file is overwritten
     * @param string $content - file content
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT
     * @param bool $dryRun - If set, file content goes to stdout rathern than file
     */
    public function createCodeFile($overwrite = false, $content = '', $mode = MODE_BOOTSTRAP, $dryRun = false) {
        $tplName = strtolower($this->dbClass);
        if ($mode == MODE_BOOTSTRAP) {
            if ($tplName == 'modplugin' || $tplName == 'modsnippet') {
                $tplName = 'phpfile.php';
            }
            $tpl = $this->helpers->getTpl($tplName);
            $tpl = str_replace('[[+elementType]]', strtolower(substr($this->dbClass, 3)), $tpl);
            $tpl = str_replace('[[+elementName]]', $this->getName(), $tpl);
            if (isset($this->myFields['description'])) {
                $description = $this->myFields['description'];
                if (strstr($description, '~~')) {
                    $split = explode('~~', $description);
                    $description = $split[1];
                }
                $tpl = str_replace('[[+description]]', $description, $tpl);
            }
            if (!empty ($tpl)) {
                $tpl = $this->helpers->replaceTags($tpl);
            }
        } elseif ($mode == MODE_EXPORT) {
            $tpl = $content;
            if ($tplName == 'modplugin' || $tplName == 'modsnippet') {
                $tag = '<' . '?' . 'php';
                if (strpos($tpl,$tag) === false) {
                    $tpl = $tag . "\n" . $tpl;
                }
            }
        }

        if (!empty($tpl)) {
            if ($this->dbClass == 'modResource') {
                $dir = $this->helpers->props['targetRoot'] . '_build/data/resources/';
            } else {
                $dir = $this->helpers->props['targetRoot'] . 'core/components/';
                $dir .= $this->helpers->props['packageNameLower'] . '/';
                $dir = $this->helpers->getCodeDir($dir, $this->dbClass);
            }
            if (isset($this->myFields['filename'])) {
                $file = $this->myFields['filename'];
            } else {
                $file = $this->helpers->getFileName($this->getName(), $this->dbClass);
            }
            if ( (! file_exists(($dir . '/' . $file))  || $overwrite)) {
                $this->helpers->writeFile($dir, $file, $tpl, $dryRun);
            } else {
                $content = file_get_contents($dir .'/' . $file);
                /* use Tpl for static elements files with minimal content
                  (modx creates them empty on addToModx() ) */
                if ( (strlen($content) < 5) && isset($this->myFields['static']) &&
                    ( !empty($this->myFields['static']))) {
                    $this->helpers->writeFile($dir, $file, $tpl, $dryRun);
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                        $this->modx->lexicon('mc_file_already_exists')
                        . ': ' . $file);
                }
            }
        }
    }


    /**
     * Create a transport file
     * @param $helpers Helpers - Helpers class
     * @param $objects array - array of objects to process
     * @param $category string
     * @param $type string - modSnippet, modChunk, etc.
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT (overwrites on export)
     */
    public static function createTransportFile(&$helpers, $objects, $category, $type,  $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        if (empty($objects)) {
            return;
        }

        $category = strtolower($category);
        /* convert 'modSnippet' to 'Snippets' */
        $variableName = lcfirst(substr($type, 3) . 's');
        $alias = $helpers->getNameAlias($type);
        $path = $helpers->props['targetRoot'] . '_build/data/';
        if (!empty($category)) {
            $path .= $category . '/';
        }
        $dryRun = $mode == MODE_EXPORT && !empty($helpers->props['dryRun']);

        // Get the Transport File Name
        $transportFile = $helpers->getFileName('', $type, 'transport');
        $fileExists = file_exists($path . $transportFile);
        if (stristr($variableName, 'menus')) { /* note: may change in Revo 2.3 */
            $variableName = 'actions';
        }

        /* Abort if file exists and not in Export mode */
        if ($fileExists && $mode != MODE_EXPORT) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $helpers->modx->lexicon('mc_file_already_exists')
                    . ': ' .  $transportFile);
            return;
        }

        /* write transport header */
        $tpl = $helpers->getTpl('transportfile.php');
        $tpl = str_replace('[[+elementType]]', $variableName, $tpl);
        $tpl = $helpers->replaceTags($tpl);
        $tpl .= '/' . '*' .  ' @var xPDOObject[] ' . '$' . $variableName .  ' *' ."/\n\n";


        $tpl .= "\n\$" . $variableName . " = array();\n\n";
        $i = 1;

        // append the code (returned from writeObject) for each object to $tpl
        foreach ($objects as $k => $fields) {
            if (isset($fields['filename'])) {
                $fileName = $fields['filename'];
                unset($fields['filename']);
            } else {
                $fileName = $helpers->getFileName($fields[$alias], $type);
            }
            $tpl .= self::writeObject($helpers, $fields, $type, $fileName, $i);
            $i++;
        }
        // write transport footer
        $tpl .= 'return $' . $variableName . ";\n";

        if (! $fileExists || $mode != MODE_BOOTSTRAP) {
            $helpers->writeFile($path, $transportFile, $tpl, $dryRun);
        } else {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $helpers->modx->lexicon('mc_file_already_exists')
                    . ': ' . $transportFile);
        }
        unset($tpl);
    }

    /**
     * Creates code for an individual element to be written to transport file
     * and properties file for any objects with properties
     *
     * @param $helpers Helpers - Helpers class
     * @param $fields array - Object fields
     * @param $type - type of object ('plugin', 'snippet', etc.)
     * @param $fileName string - filename of the object's code file
     * @param $i int - index of element in transport file
     * @return string - code for this object to be inserted in transport file (by $this->process())
     */
    public static function writeObject(&$helpers, $fields, $type, $fileName, $i) {
        $variableName = lcfirst(substr($type, 3) . 's');
        /* write generic stuff */
        $tpl = '$' . $variableName . '[' . $i . '] = $modx->newObject(' . "'" . $type . "');" . "\n";
        $tpl .= '$' . $variableName . '[' . $i . ']->fromArray(';
        // $tpl .= "    'id' => " . $i . ",\n";

        /* Set id field to $i, but only for objects with an ID */
        if (isset($fields['id'])) {
            unset ($fields['id']);
            $fields = array_merge(array('id' => $i), $fields);
        }


        /* This may not be necessary */
        /* *********** */

        $properties = isset($fields['properties'])? $fields['properties'] : array();
        $hasProperties = false;
        if (!empty($properties)) {
            /* properties file is written by the element and resource adapters */
            $hasProperties = true;
            unset($fields['properties']);
        } else {
            // $fields['properties'] ='';
        }
        /* ************  */
        if (isset($fields['static'])
            && (!empty($fields['static']))
            && isset($fields['static_file'])
            && (!empty($fields['static_file']))
        ) {
            $source = "MODX_BASE_PATH . " . "'" .
                $fields['static_file'] . "'";
        } else {
            $source = "\$sources['source_core'] . '/elements/" .
                strtolower($variableName) . '/' . $fileName . "'";
        }

        unset(
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

        $tpl .= var_export($fields, true);

        /* finish up */
        $tpl .= ", '', true, true);\n";

        if ($type == 'modResource') {
            $tpl .= "\$resources[" . $i . "]->setContent(file_get_contents(\$sources['data']." . "'resources/" . $fileName . "'));\n\n";
        } elseif ($type == 'modChunk' || $type == 'modSnippet' || $type == 'modPlugin' || $type == 'modTemplate') {

            $tpl .= '$' . $variableName . '[' . $i . "]->setContent(file_get_contents(" . $source . "));\n\n" ;
        }

        /* handle properties */

        if ($hasProperties) {
            /* @var $helpers Helpers */
            $alias = $helpers->getNameAlias($type);
            $name = $fields[$alias];
            $propertyFileName = $helpers->getFileName($name, $type, 'properties');
            $tpl .= "\n\$properties = include \$sources['data'].'properties/" . $propertyFileName ."';\n" ;
            $tpl .= '$' . $variableName . "[" . $i . "]->setProperties(\$properties);\n";
            $tpl .= "unset(\$properties);\n\n";

        }
        return $tpl;
    }

    public function writePropertiesFile($objectName, $properties, $mode = MODE_BOOTSTRAP, $dryRun = false) {
        $dir = $this->helpers->props['targetRoot'] . '_build/data/properties/';
        $fileName = $this->helpers->getFileName($this->getName(),
            $this->dbClass, 'properties');
        if (file_exists($dir . $fileName) && $mode != MODE_EXPORT) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                $this->modx->lexicon('mc_file_already_exists')
                . ': ' . $fileName);
        } else {
            $tpl = $this->helpers->getTpl('propertiesfile.php');
            $tpl = str_replace('[[+element]]', $objectName, $tpl);
            $tpl = str_replace('[[+elementType]]', substr(strtolower($this->dbClass), 3), $tpl);

            $tpl = $this->helpers->replaceTags($tpl);
            /* Add php tag if not there. Some servers balk
               if they see an intact php tag.
            */
            $hastags = strpos($tpl, '<' . '?' . 'php');
            if ($hastags === false)
                $tpl = '<' . '?' . 'php' . $tpl;
            $tpl .= "\n\n" . $this->render_properties($properties) . "\n\n";

            if ($dryRun) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_would_be_creating')
                    . ': ' . $fileName . "\n");
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_begin_file_content'));
            }
            $this->helpers->writeFile($dir, $fileName, $tpl, $dryRun);
            if ($dryRun) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_end_file_content')
                    . "\n");
            }
            unset($tpl);
        }
    }

    /**
     * Function to write the code for the build properties file.
     * @param $arr array - array of properties
     * @return string - code for the elements properties
     */
    private function render_properties($arr) {
        foreach ($arr as $k => $fields) {
            unset($arr[$k]['desc_trans']);
            unset($arr[$k]['area_trans']);
        }
        $output =  '$properties = ';
        $output .= var_export($arr, true);
        $output .= ";\n\nreturn \$properties;";

        return $output;
    }

}