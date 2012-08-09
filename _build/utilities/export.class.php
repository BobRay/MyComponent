<?php
/**
 * MyComponent exportobjects.php
 * @author Bob Ray
 *
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
 * Class for MyComponent exportobjects utility
 */
class Export
{
    /* @var modx modX */
    var $modx;
    var $props;
    var $elements;
    var $category;
    var $categoryId;
    var $parents; //array of parents
    var $includeParents; // should parent resources be included
    var $pagetitles; // array of pagetitles
    var $source; // path to root of MyComponent
    var $sourceCore; // path to MyComponent Core directory
    var $targetBase; // base path to new component
    var $targetCore; // path to new component core dir
    var $elementPath;
    var $resourcePath;
    var $packageName;
    var $packageNameLower;
    var $filePath;
    var $transportPath;
    var $createTransportFiles;
    var $createObjectFiles;
    var $dirPermission;

    var $elementType;
    /* @var $categoryObj modCategory */
    var $categoryObj;
    var $dryRun;
    /* @var $helpers Helpers */
    var $helpers; /* helpers class */




    function  __construct(&$modx, &$props = array()) {
            $this->modx =& $modx;
            $this->props =& $props;
    }

    function init() {
        clearstatcache(); /*  make sure is_dir() is current */
        $config = dirname(dirname(__FILE__)) . '/build.config.php';
        if (file_exists($config)) {
            $configProps = @include $config;
        } else {
            die('Could not find main config file at ' . $config);
        }
        //$configProps = include $configFile;
        if (empty($configProps)) {
            die('Could not find project config file at ' . $configFile);
        }
        $this->props = array_merge($configProps, $this->props);
        unset($config, $configFile, $configProps);

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

        $this->packageName = $this->modx->getOption('packageName', $this->props, '');
        $this->packageNameLower = $this->modx->getOption('packageNameLower', $this->props, '');

        $this->category = $this->modx->getOption('category', $this->props, '');
        $parents = $this->modx->getOption('parents', $this->props,null);
        $this->parents =  $parents ? explode(',',$parents): array();
        $this->includeParents = $this->modx->getOption('includeParents', $this->props,false);

        $pagetitles = $this->modx->getOption('pagetitles', $this->props,null);
        $this->pagetitles =  $pagetitles ? explode(',',$pagetitles): array();

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

    function process($element)
    {
        if (stristr($element,'menus')) { /* note: may change in Revo 2.3 */
            $element='Actions';
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, "\n\n<h3>Processing " . $element . '</h3>');
        
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Category: ' . $this->category);
        /* convert 'chunks' to 'modChunk' etc. */
        $this->elementType = 'mod' . substr(ucFirst($element),0,-1);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Element Type: ' . $this->elementType);

        if ($this->elementType == 'modResource') {
            $this->pullResources();
        } else {
            $key = $this->elementType == 'modSystemSetting' || $this->elementType =='modAction' ? 'namespace' : 'category';
            $value = $this->elementType == 'modSystemSetting' || $this->elementType =='modAction'  ? strtolower($this->category) : $this->categoryId;
            $this->elements = $this->modx->getCollection($this->elementType, array($key => $value));
            if (empty($this->elements) && $this->elementType == 'modSystemSetting') {
                /* try again with actual category for system settings*/
                $value = $this->category;
                $this->elements = $this->modx->getCollection($this->elementType, array($key => $value));
            }
        }

        if (empty($this->elements)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'No objects found in category: ' . $this->category);
            return;
        }


        $transportFile = 'transport.' . strtolower($element) . '.php';
        $transportDir = str_replace('systemsettings', 'settings', $this->transportPath);
        $transportDir = str_replace('templatevars', 'tvs', $transportDir);

        /* write transport header */
        $tpl = $this->helpers->getTpl('transportfile.php');
        $tpl = str_replace('[[+elementType]]', $element, $tpl);
        $tpl = $this->helpers->replaceTags($tpl);

        $tpl .= "\n\$" . strtolower($element) . " = array();\n\n";

        $i=1;
        foreach($this->elements as $elementObj) {
            $tpl .= $this->writeObject($elementObj, strtolower(substr($element, 0, -1)), $i);
            if ($this->props['createObjectFiles']) {
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
    /* Writes individual object to transport file */

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
            $tpl .= "    '" . $field . "'" . " => '" . $value . "',\n";
        }
        /* ToDo: Property Sets */
        /* write object-specific stuff */
        switch ($this->elementType) {

            case 'modChunk':
                $tpl .= "    'snippet' => file_get_contents(\$sources['source_core']." . "'/elements/chunks/" . $this->makeFileName($elementObj) . "'),\n";
                break;

            case 'modSnippet':
                $tpl .= "    'snippet' => stripPhpTags(\$sources['source_core']." . "'/elements/snippets/" . $this->makeFileName($elementObj) . "'),\n";
                break;

            case 'modPlugin':
                $tpl .= "    'plugincode' => stripPhpTags(\$sources['source_core']." . "'/elements/plugins/" . $this->makeFileName($elementObj) . "'),\n";
                break;

            case 'modTemplate':
                $tpl .= "    'content' => file_get_contents(\$sources['source_core']." . "'/elements/templates/" . $this->makeFileName($elementObj) . "'),\n";
                break;

            default:
                break;
        }
        /* finish up */
        $tpl .= "), '', true, true);\n";

        if ($this->elementType == 'modResource') {
            $tpl .= "\$resources[" . $i . "]->setContent(file_get_contents(\$sources['data']." . "'resources/" . $this->makeFileName($elementObj) . "'));\n\n";
        }

        /* handle properties */
        if ($hasProperties) {
            $tpl .= "\n\$properties = include \$sources['data'].'properties/properties." . strtolower($elementObj->get('name')) . ".php';\n";
            $tpl .= '$' . $element . "s[" . $i . "]->setProperties(\$properties);\n";
            $tpl .= "unset(\$properties);\n\n";
            $this->writePropertyFile($properties, 'properties.' . strtolower($elementObj->get('name')) . '.php', $elementObj->get('name'));
        }
        return $tpl;
    }
    protected function writePropertyFile($properties, $fileName, $objectName) {
        $dir = $this->transportPath . 'properties/';
        $tpl = $this->helpers->getTpl('propertiesfile.php');
        $tpl = str_replace('[[+element]]',$objectName,$tpl);
        $tpl = str_replace('[[+elementType]]', substr(strtolower($this->elementType), 3), $tpl);

        $tpl = $this->helpers->replaceTags($tpl);
        // $s = $this->render_properties($properties);
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

    function render_properties( $arr, $depth=-1, $tabWidth=4) {

        if ($depth == -1) {
            /* this will only happen once */
            unset($arr['desc_trans'], $arr['area_trans']);
            $output = "\$properties = array( \n";
            $depth++;
        } else {
            $output = "array( \n";
        }
        $indent = str_repeat( " ", $depth + $tabWidth );

        foreach( $arr as $key => $val ) {
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


    protected function createObjectFile ($elementObj, $element) {

        /* @var $elementObj modElement */

        if ($elementObj->get('static')) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Skipping object file for static object: ' . $elementObj->get('name'));
            return;
        }
        $fileName = $this->makeFileName($elementObj);
        if ($fileName) {
            $content = $elementObj->getContent();
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Skipping object file for: ' . $this->elementType . '; object (does not need source file)');
            return;
        }
        if ($this->elementType == 'modResource') {
            $dir = $this->resourcePath;
        } else {
            $dir = $this->elementPath . '/' . $element;
        }
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, '    Would be creating: ' . $fileName . "\n");
            $this->modx->log(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
        }
        $tpl = '';
        if ($this->elementType == 'modSnippet' || $this->elementType == 'modPlugin') {
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

    protected function makeFileName($elementObj) {
        /* $elementType is in the form 'modSnippet', 'modChunk', etc.
         * set default suffix to 'chunk', 'snippet', etc. */

        /* @var $elementObj modElement */
        $suffix = substr(strtolower($this->elementType),3);

        $extension = 'php';
        switch ($this->elementType) {
            case 'modTemplate':
                $name = $elementObj->get('templatename');
                $extension = 'html';
                break;
            case 'modChunk':
                $extension = 'html';
                /* intentional fallthrough */
            case 'modSnippet':
            case 'modPlugin':
                $name = $elementObj->get('name');
                break;
            case 'modResource':
                $name = $elementObj->get('pagetitle');
                $extension = 'html';
                $suffix = 'content';
                break;
            default:
               $name = '';
                break;

        }
        /* replace spaces with underscore */
        $name = str_replace(' ', '_', $name);
        return $name? strtolower($name) . '.' . $suffix . '.' . $extension : '';

    }

    public function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
    }

}
