<?php
/**
 * exportobjects.php file for MyComponent Extra
 *
 * @author Bob Ray
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 *
 * MyComponent is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package MyComponent
 */

/** Description:
 * -------------
 * Methods used by exportobjects.php in MyComponent Extra
 */
class Export
{
    /* @var modx modX */
    public $modx;
    /* @var $props array */
    public $props;
    /* @var $helpers Helpers */
    public $helpers; /* helpers class */
    protected $elements;
    public $category;
    protected $categoryId;
    protected $parents; //array of parents
    protected $includeParents; // should parent resources be included
    protected $pagetitles; // array of pagetitles
    protected $source; // path to root of MyComponent
    protected $sourceCore; // path to MyComponent Core directory
    protected $targetBase; // base path to new component
    protected $targetCore; // path to new component core dir
    protected $elementPath;
    protected $resourcePath;
    protected $packageName;
    protected $packageNameLower;
    protected $filePath;
    protected $transportPath;
    protected $createTransportFiles;
    protected $createObjectFiles;
    protected $dirPermission;
    protected $elementType;
    /* @var $categoryObj modCategory */
    protected $categoryObj;
    protected $dryRun;
    
    




    function  __construct(&$modx, &$config = array()) {
            $this->modx =& $modx;
            $this->props =& $config;
    }

    /** Initializes class variables */
    public function init($configFile) {
        clearstatcache(); /*  make sure is_dir() is current */
        //$configFile = dirname(dirname(__FILE__)) . '/build.config.php';
        if (file_exists($configFile)) {
            $configProps = include $configFile;
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not find main config file at ' . $configFile);
            die();
        }

        if (empty($configProps)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not find project config file at ' . $configFile);
            die();
        }
        /* properties sent to constructor will override those in config file */
        $this->props = array_merge($configProps, $this->props);
        unset($configFile, $configProps);

        $this->source = $this->props['source'];
        if (empty ($this->source)) {
            die('source directory must be set');
        }
        require_once $this->source . '_build/utilities/helpers.class.php';
        $this->helpers = new Helpers($this->modx, $this->props);
        $this->helpers->init();
        $this->dirPermission = $this->props['dirPermission'];
        $this->dryRun = (isset($this->props['dryRun']) && $this->props['dryRun']) || (empty($this->props['createTransportFiles']) &&  empty($this->props['createObjectFiles']));
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO,'Dry Run');
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO,'Not a Dry Run');
        }

        $this->packageName = $this->props['packageName'];
        $this->packageNameLower = $this->props['packageNameLower'];

        $this->category = $this->props['category'];
        $parents = $this->modx->getOption('parents', $this->props,null);
        $this->parents =  !empty($parents) ? explode(',',$parents): array();
        $this->includeParents = $this->modx->getOption('includeParents', $this->props,false);

        $pagetitles = $this->modx->getOption('pagetitles', $this->props,null);
        $this->pagetitles =  !empty($pagetitles) ? explode(',',$pagetitles): array();


        $this->createObjectFiles = $this->modx->getOption('createObjectFiles',$this->props,false);
        $this->createTransportFiles = $this->modx->getOption('createTransportFiles',$this->props,false);

        /* add trailing slash if missing */
        if(substr($this->source, -1) != "/") {
            $this->source .= "/";
        }

        $this->targetBase = MODX_BASE_PATH . 'assets/mycomponents/' . $this->packageNameLower . '/';
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';

        $this->resourcePath = $this->targetBase . '_build/data/resources';
        $this->elementPath = $this->targetCore . 'elements';

        $this->transportPath = $this->targetBase . '_build/data/';
        
        if (empty($this->category)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Category must be set');
                return false;
        }
        $this->categoryObj = $this->modx->getObject('modCategory', array('category'=> $this->category));
        if (! $this->categoryObj) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Could not find category:' . $this->props['category']);
            return false;
        }
        $this->categoryId = $this->categoryObj->get('id');
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Category ID: ' . $this->categoryId);
        /* dry run if user has set &dryRun=`1` or has not set either create option */

        return true;
    }


    /**
     * Processes all elements of specified type that are in the category or area
     * (resources are specified by parent and/or list of pagetitles).
     *
     * (optionally) writes code file and transport file
     *
     * @param $element - string element type('snippets', 'plugins' etc.)
     */
    public function process($element)
    {
        if (stristr($element,'menus')) { /* note: may change in Revo 2.3 */
            $element='Actions';
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, "\n\nProcessing " . $element);
        
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Category: ' . $this->category);
        /* convert 'chunks' to 'modChunk' etc. */
        $this->elementType = 'mod' . substr(ucFirst($element),0,-1);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Element Type: ' . $this->elementType);
        if ($this->elementType == 'modResource') {
            $this->pullResources();
        } else {
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
            $tpl .= $this->writeObject($elementObj, strtolower(substr($element, 0, -1)), $i);
            if ($this->createObjectFiles) {
                $this->createObjectFile($elementObj, $element);
            }
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
     * Creates code for an individual element to be written to transport file
     * and properties file for any objects with properties
     *
     * @param $elementObj - MODX object (the element)
     * @param $element - type of object ('plugin', 'snippet', etc.)
     * @param $i int - index of element in transport file
     * @return string - code for this object to be inserted in transport file (by $this->process())
     */
    protected function writeObject($elementObj, $element, $i) {
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

    /**
     * Writes the properties file for objects with properties
     * @param $properties array - object properties as PHP array
     * @param $fileName - Name of properties file
     * @param $objectName - Name of MODX object
     */
    protected function writePropertyFile($properties, $fileName, $objectName) {
        $dir = $this->transportPath . 'properties/';
        $tpl = $this->helpers->getTpl('propertiesfile.php');
        $tpl = str_replace('[[+element]]',$objectName,$tpl);
        $tpl = str_replace('[[+elementType]]', substr(strtolower($this->elementType), 3), $tpl);

        $tpl = $this->helpers->replaceTags($tpl);
        $tpl .= "\n\n" . $this->render_properties($properties) . "\n\n";

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
    function render_properties( $arr, $depth=-1, $tabWidth=4) {

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
            $output .= $indent . "'$key' => ";

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


    /**
     * Creates the code file for an element or resource - skips static elements
     *
     * @param $elementObj modElement - element MODX object
     * @param $element - string name of element type ('plugin', 'snippet' etc.) used in dir name.
     */
    protected function createObjectFile ($elementObj, $element) {

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
                $tpl .= "<?php\n\n";
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

}
