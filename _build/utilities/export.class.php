<?php
/**
 * MyComponent
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
    var $basePath;
    var $corePath;
    var $elementPath;
    var $resourcePath;
    var $packageName;
    var $filePath;
    var $transportPath;
    var $createTransportFiles;
    var $createObjectFiles;

    var $elementType;
    /* @var $categoryObj modCategory */
    var $categoryObj;
    var $dryRun;




    function  __construct(&$modx, &$props = array()) {
            $this->modx =& $modx;
            $this->props =& $props;
    }

    function init($category, $packageName = null) {
        clearstatcache(); /*  make sure is_dir() is current */
        $this->dryRun = (isset($this->props['dryRun']) && $this->props['dryRun']) || (empty($this->props['createTransportFiles']) &&  empty($this->props['createObjectFiles']));
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO,'Dry Run');
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO,'Not a Dry Run');
        }

        $this->packageName = ! empty($packageName)
            ?strtolower($packageName)
            :strtolower($category);
        $this->category = $category; 
        $parents = $this->modx->getOption('parents', $this->props,null);
        $this->parents =  $parents ? explode(',',$parents): array();
        $this->includeParents = $this->modx->getOption('includeParents', $this->props,false);

        $pagetitles = $this->modx->getOption('pagetitles', $this->props,null);
        $this->pagetitles =  $pagetitles ? explode(',',$pagetitles): array();

        $this->createObjectFiles = $this->modx->getOption('createObjectFiles',$this->props,false);
        $this->createTransportFiles = $this->modx->getOption('createTransportFiles',$this->props,false);

        $this->basePath = $this->modx->getOption('basePath',$this->props, null);
        if (empty ($this->basePath) ) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "&basePath must be set");
            return false;
        }
        /* add trailing slash if missing */
        if(substr($this->basePath, -1) != "/") $this->basePath .= "/";
        
        $this->corePath = $this->modx->getOption('corePath', $this->props,$this->basePath . 'core/components/' . $this->packageName . '/');

            $this->resourcePath = $this->basePath . '_build/data/resources/';
            $this->elementPath = $this->corePath . 'elements/';

        if (!is_dir($this->elementPath)) {
            if (!$this->dryRun) {
                if (!mkDir($this->elementPath, 0, true)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to create element path: ' . $this->elementPath);
                    return false;
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Would be creating element path: ' . $this->elementPath);
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'element path: ' . $this->elementPath);
        }
        $this->transportPath = $this->basePath . '_build/data/';
        
        if (! is_dir($this->transportPath)) {
            if (! $this->dryRun) {
                if (! mkDir($this->transportPath, 0, true)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to create transport path: ' . $this->transportPath);
                    return false;
                } else {
                    $this->modx->log(modX::LOG_LEVEL_INFO, 'Created transport path: ' . $this->transportPath);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Would be creating transport path: ' . $this->transportPath);
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Transport path: ' . $this->transportPath);
        }
        
        
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

        /* dry run if user has set &dryRun=`1` or has not set either create option */


        return true;
    }

    function process($element)
    {
        if (stristr($element,'menu')) { /* note: may change in Revo 2.3 */
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

        $transportFile = $this->transportPath . 'transport.' . strtolower($element) . '.php';
        $transportFile = str_replace('systemsettings','settings',$transportFile);
        $transportFile = str_replace('templatevars','tvs',$transportFile);
        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, ' Would be writing to: ' . $transportFile);
            $transportFp = fopen('php://output','w');
        } else {
            $transportFp = fopen($transportFile, 'w');
        }
        if (!$transportFp) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Could not open transport file: ' . $transportFile);
        }
        /* write transport header */
        fwrite($transportFp, "<?php\n");
        $this->writeLicense($transportFp);
        fwrite($transportFp, "\n\$" . strtolower($element) . " = array();\n\n");
        $i=1;
        foreach($this->elements as $elementObj) {
            $this->writeObject($transportFp,$elementObj,strtolower(substr($element,0,-1)),$i);
            if ($this->props['createObjectFiles']) {
                $this->createObjectFile($elementObj, $element);
            }
            $i++;
        }
        /* write transport footer */
        fwrite($transportFp, 'return $' . strtolower($element) . ";\n");
        fclose($transportFp);
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
        /* add children of  from pagetitle array to $this->elements */
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

    protected function writeObject($transportFp, $elementObj, $element, $i) {
        /* element is in the form 'chunk', 'snippet', etc. */
        /* @var $elementObj modElement */

        /* write generic stuff */
        fwrite($transportFp, '$' . $element . 's[' . $i . '] = $modx->newObject(' . "'" . $this->elementType . "');" . "\n");
        fwrite($transportFp, '$' . $element . 's[' . $i . '] ->fromArray(array(' . "\n");
        fwrite($transportFp, "    'id' => " . $i . ",\n");
        $fields = $elementObj->toArray('',true);  // true gets raw values - check this
        $properties = $elementObj->get('properties');
        if (!empty($properties)) {
            /* handled below */
            unset($fields['properties']);
        } else {
            ($fields['properties'] ='');
        }
        unset($fields['id'],
            $fields['snippet'],
            $fields['content'],
            $fields['plugin'],
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
            $fields['editedon']
        );

        foreach ($fields as $field => $value) {
            fwrite($transportFp, "    '" . $field . "'" . " => '" . $value . "',\n");
        }
        /* ToDo: Property Sets */
        /* write object-specific stuff */
        switch ($this->elementType) {
            case 'modChunk':
                fwrite($transportFp, "    'snippet' => file_get_contents(\$sources['source_core']." . "'/elements/chunks/" . $this->makeFileName($elementObj) . "'),\n");
                break;
            case 'modSnippet':
                fwrite($transportFp, "    'snippet' => getSnippetContent(\$sources['source_core']." . "'/elements/snippets/" . $this->makeFileName($elementObj) . "'),\n");
                break;
            case 'modPlugin':
                fwrite($transportFp, "    'plugin' => getSnippetContent(\$sources['source_core']." . "'/elements/plugins/" . $this->makeFileName($elementObj) . "'),\n");

            case 'modTemplate':

                fwrite($transportFp, "    'content' => file_get_contents(\$sources['source_core']." . "'/elements/templates/" . $this->makeFileName($elementObj) . "'),\n");
                break;


            default:
                break;
        }
        /* finish up */
        fwrite($transportFp, "),'',true,true);\n");

        if ($this->elementType == 'modResource') {
            fwrite($transportFp, "\$resources[" . $i . "]->setContent(file_get_contents(\$sources['data']." . "'resources/" . $this->makeFileName($elementObj) . "'));\n\n");
        }

        /* handle properties */
        if (! empty($properties)) {
            fwrite($transportFp,"\n\$properties = include \$sources['data'].'properties/properties." . strtolower($elementObj->get('name')) .".php';\n");
            fwrite($transportFp,"\$plugins[" . $i . "]->setProperties(\$properties);\n");
            fwrite($transportFp,"unset(\$properties);\n");
            $this->writePropertyFile($properties, 'properties.' . strtolower($elementObj->get('name')) . '.php');
        }

    }
    protected function writePropertyFile($properties, $fileName) {
        $path = $this->transportPath . 'properties/';
        if (!is_dir($path) ) {
            if (! $this->dryRun) {
                if (! mkdir($path,0,true)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to create directory: ' . $path);
                } else {
                    $this->modx->log(modX::LOG_LEVEL_INFO, 'Created directory: ' . $path);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Would be creating directory: ' . $path);
            }
        }
        $file = $path . $fileName;
        if ($this->dryRun) {
            $fp = fopen('php://output','w');
        } else {
            $fp = fopen($file,'w');
        }
        fwrite($fp, "<?php\n");
        $this->writeLicense($fp);
        fwrite($fp,
         "/**\n" .
         '* Default properties for the ' . $fileName . "snippet\n" .
         '* @author ' . $this->props['authorName'] . ' ' . $this->props['authorEmail'] . "\n" .
         "*\n" .
         '* @package ' . strtolower($this->props['packageName']) . "\n" .
         "* @subpackage build\n"  .
         "*/\n\n"
        );

        $s = $this->render_properties($properties);

        fwrite($fp,"\n\n" . $s . "\n");

        fwrite ($fp,"\n");
        fclose($fp);
    }

    function render_properties( $arr, $depth=-1, $tabWidth=4) {
        /* this will only happen once */
        if ($depth == -1) {
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
        $name = $this->makeFileName($elementObj);
        if ($name) {
            $content = $elementObj->getContent();
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Skipping object file for: ' . $this->elementType . '; object (does not need source file)');
            return;
        }
        if ($this->elementType == 'modResource') {
            $path = $this->resourcePath;
        } else {
            $path = $this->elementPath . $element . '/';
        }
        if (! is_dir($path))
            if( !$this->dryRun) {
                if (!mkdir($path,0,true)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to create directory: ' . $path);
                } else {
                    $this->modx->log(modX::LOG_LEVEL_INFO, 'Created directory: ' . $path);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Would be creating directory: ' . $path);
            }

        $path .=  $name;

        if ($this->dryRun) {
            $this->modx->log(modX::LOG_LEVEL_INFO, ' Would be writing to: ' . $path);
            $fileFp = fopen('php://output', 'w');
        } else {
            $fileFp = fopen($path, 'w');
        }
        if (!$fileFp) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not open transport file: ' . $path);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, "\n\n --- Begin File Content --- ");
        if ($this->elementType == 'modSnippet' || $this->elementType == 'modPlugin') {
            if (! strstr($content, '<?')) {
                fwrite($fileFp,"<?php\n\n");
            }


            if (strstr($content,'GNU') && ! stristr($content,'License') ) {
                $this->writeLicense($fileFp);
            }
        }

        fwrite($fileFp,$content);
        fclose($fileFp);
        $this->modx->log(modX::LOG_LEVEL_INFO, "\n --- End File Content --- \n");


    }

    protected function makeFileName($elementObj) {
        /* $element is now in the form 'chunk', 'templatevar Chunks to chunk, etc. */
        /* set default suffix to 'chunk', 'snippet', etc. */
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
        return $name? strtolower($name) . '.' . $suffix . '.' . $extension : '';

    }
    protected function writeLicense($fp) {
        $tpl = file_get_contents(MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/licensetpl.php');
        if (empty($tpl)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "License Tpl is empty");
            return;
        }
        $fields = array(
            '[[+packageName]]' => $this->props['packageName'],
            '[[+packageNameLower]]' => strtolower($this->props['packageName']),
            '[[+authorName]]' => $this->props['authorName'],
            '[[+authorEmail]]' => $this->props['authorEmail'],
            '[[+year]]' => strftime('%Y')

        );

        $tpl =  $this->strReplaceAssoc($fields,$tpl);
        fwrite($fp,$tpl);

    }
    public function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
    }

}
