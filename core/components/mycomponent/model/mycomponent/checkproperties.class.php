<?php
/**
 * checkproperties script file for MyComponent extra
 *
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 * Created on 08-11-2012
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
 * @package mycomponent
 */

/**
 * Description
 * -----------
 * methods used by checkproperties.php
 *
 * @package mycomponent
 **/


class CheckProperties {
    /* @var $props array */
    public $props;
    public $targetBase;
    public $targetCore;
    public $category;
    public $classFiles;
    public $included;
    public $scriptCode;
    public $output;
    public $spAliases;
    public $codeMatches;
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;
    public $source;
    public $packageNameLower;


    function  __construct(&$modx, &$props = array()) {
        /* @var $modx modX */
        $this->modx =& $modx;
        $this->props =& $props;
    }

    public function init($scriptProperties = array()) {
        clearstatcache(); /*  make sure is_dir() is current */

        require dirname(__FILE__) . '/mcautoload.php';
        spl_autoload_register('mc_auto_load');
        // Get the project config file
        $currentProject = '';
        $currentProjectPath = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/') . '_build/config/current.project.php';
        if (file_exists($currentProjectPath)) {
            include $currentProjectPath;
        } else {
            die('Could not find current.project.php file at: ' . $currentProjectPath);
        }

        $projectConfigPath = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/') .
            '_build/config/' . strtoLower($currentProject) . '.config.php';

        if (file_exists($projectConfigPath)) {
            $properties = include $projectConfigPath;
        } else {
            die('Could not find Project Config file at: ' . $projectConfigPath);
        }

        /* Make sure that we get usable values */
        if (!is_array($properties) or empty($properties)) {
            die('Config File was not set up correctly: ' . $projectConfigPath);
        }

        $this->props = isset($this->props)
            ? $this->props
            : array();
        $this->props = array_merge($properties, $this->props);
        unset($currentProjectPath, $projectConfigPath);

        // include 'helpers.class.php'
        $this->helpers = new Helpers($this->modx, $this->props);
        $this->helpers->init();

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Project: ' . $this->props['packageName']);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "Action: Check Properties\n");
        $this->source = $this->props['mycomponentRoot'];
        /* add trailing slash if missing */
        if (substr($this->source, -1) != "/") {
            $this->source .= "/";
        }


        $this->packageNameLower = $this->props['packageNameLower'];
        $this->targetBase = $this->props['targetRoot'];
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';
        //$this->targetAssets = $this->targetBase . 'assets/components/' . $this->packageNameLower . '/';
        /*$this->primaryLanguage = $this->modx->getOption('primaryLanguage', $this->props, '');
        if (empty($this->primaryLanguage)) {
            $this->primaryLanguage = 'en';
        }*/
        clearstatcache(); /*  make sure is_dir() is current */


        $aliases = $this->props['scriptPropertiesAliases'];

        $this->spAliases = array();
        foreach ($aliases as $alias) {
            $this->spAliases[] = '\$' . $alias;
            $this->spAliases[] = '\$' . 'this->' . $alias;
        }

        $this->included = array();
        $this->output = '';
    }
    public function run() {
        $snippets = $this->modx->getOption('snippets', $this->props['elements'], array());
        $elements = array();
        /* get all plugins and snippets from config file */
        foreach ($snippets as $snippet => $fields) {
            if (isset($fields['name'])) {
                $snippet = $fields['name'];
            }
            $elements[strtolower(trim($snippet))] = 'modSnippet';
        }
        $plugins = $this->modx->getOption('plugins',$this->props['elements'], array());
        foreach ($plugins as $plugin => $fields) {
            if (isset($fields['name'])) {
                $plugin = $fields['name'];
            }
            $elements[strtolower(trim($plugin))] = 'modPlugin';
        }
        $this->classFiles = array();
        $dir = $this->targetCore . 'model';
        $this->helpers->dirWalk($dir, null, true);
        $this->classFiles = $this->helpers->getFiles();
        if(!empty($this->classFiles)) {
            $this->output .= "\nFound these class files: " . implode(', ', array_keys($this->classFiles));

        }

        /* process each element */
        foreach($elements as $element => $type) {
            $this->included = array();
            $this->scriptCode = '';
            $this->codeMatches = array();
            $this->getCode($element, $type);
            if (!empty($this->included)) {
                $this->output .= "\nFiles analyzed: " . implode(', ', $this->included);
            }
            $this->output .= "\nSize of all code: " . strlen($this->scriptCode);

            //$this->output .= "\n ********************************* \n" . $this->scriptCode;
            $this->getProperties();
            $this->output .= "\n" . count($this->codeMatches) . ' properties in code';
            $this->checkProperties($element, $type);
        }
        $this->report();
    }


    /**
     * returns raw code from an element file and all
     * the class files it includes
     * 
     * @param $element array member
     * @param $type string - 'modSnippet or modChunk
     */
    public function getCode($element, $type) {
        if (empty($element)) {
            $this->output .= 'Error: Element is empty';
            return;
        }
        $typeName = strtolower(substr($type, 3));
        $file = $this->targetCore . 'elements/' . $typeName . 's/' . $element . '.' . $typeName . '.php';
        $this->output .= "\n\n*********************************************";
        $this->output .= "\n" . 'Processing Element: ' . $element . " -- Type: " . $type;
        $this->scriptCode = file_get_contents($file);
        $this->included[] = $element;
        $this->getIncludes($file);

    }

    /**
     * Searches for included .php files in code
     * and appends their content to $code reference var
     *
     * @param $file - path to code file(s)
     */
    public function getIncludes($file) {
        $matches = array();
        $lines = array();
        $fp = fopen($file, "r");
        if ($fp) {
            while (!feof($fp)) {
                $lines[] = fgets($fp, 4096);
            }
            fclose($fp);
        } else {
            $this->output .= "\nCould not open file: " . $file;
            return;
        }
        $line = '';

        foreach ($lines as $line) {
            $fileName = 'x';
            if (strstr($line, 'include') || strstr($line, 'include_once') || strstr($line, 'require') || strstr($line, 'require_once')) {
                preg_match('#[0-9a-zA-Z_\-\s]*\.class\.php#',$line, $matches);
                $fileName = isset($matches[0])? $matches[0] : 'x';
            }
            /* check files included with getService() and loadClass() */
            if (strstr($line, 'modx->getService')) {
                $pattern = "/modx\s*->\s*getService\s*\(\s*\'[^,]*,\s*'([^']*)/";
                preg_match($pattern, $line, $matches);
                if (isset($matches[1])) {
                    $s = strtoLower($matches[1]);
                    if (strstr($s, '.')) {
                        $r = strrev($s);
                        $fileName = strrev(substr($r, 0, strpos($r, '.')));
                    }
                    else {
                        $fileName = $s;
                    }
                }
            }
            if (strstr($line, 'modx->loadClass')) {
                $pattern = "/modx\s*->\s*loadClass\s*\(\s*\'([^']*)/";
                preg_match($pattern, $line, $matches);
                if (isset($matches[1])) {
                    $s = strtoLower($matches[1]);
                    if (strstr($s, '.')) {
                        $r = strrev($s);
                        $fileName = strrev(substr($r, 0, strpos($r, '.')));
                    }
                    else {
                        $fileName = $s;
                    }
                }
            }
            $fileName = strstr($fileName, 'class.php')? $fileName : $fileName . '.class.php';

            if (isset($this->classFiles[$fileName])) {

                /* skip files we've already included */
                if (!in_array($fileName, $this->included)) {
                    $this->scriptCode .= file_get_contents($this->classFiles[$fileName] . '/' . $fileName);
                    $this->included[] = $fileName;
                    $this->getIncludes($this->classFiles[$fileName] . '/' . $fileName);
                }
            }


        }
    }
    public function getProperties() {
        foreach($this->spAliases as $key => $alias) {

            $matches = array();


            $pattern = "/" . $alias . "\[[\"\']([^\"\']+)/";

            /* get properties used with $scriptProperties['propertyName'] */
            preg_match_all($pattern, $this->scriptCode, $matches);
            $codeMatches = $matches[1];

            $matches = array();

            /* get properties accessed with getOption() */
            $pattern = "/getOption\(\'([^\']+)'.+" . $alias . "/";
            preg_match_all($pattern, $this->scriptCode, $matches);
            $codeMatches = array_merge($codeMatches, $matches[1]);
            $this->codeMatches = array_merge($this->codeMatches, $codeMatches);
        }
        /* handle properties retrieve with getProperty() */
        $matches = array();
        $pattern = "/getProperty\(\'([^\']+)'/";
        preg_match_all($pattern, $this->scriptCode, $matches);
        $codeMatches = array_merge($codeMatches, $matches[1]);
        $this->codeMatches = array_merge($this->codeMatches, $codeMatches);
    }

    public function checkProperties($element, $elementType) {
        $type = strtolower(substr($elementType, 3));
        $type = $type == 'templatevar' ? 'tv' : $type;
        $orphans = array();
        $missing = array();
        $hasCodeProperties = !empty($this->codeMatches);
        $propsFile = $this->targetBase . '_build/data/properties/properties.' . $element . '.'  . $type . '.php';
        if (file_exists($propsFile)) {
            $props = include $propsFile;
        } else {
            $this->output .= "\nNo Properties file for " . $element . ' ' . $type;
            $this->output .= "\nLooked for: " . $propsFile;
        }
        $names = array();
        if (empty($props) || ! is_array($props)) {
            $props = array();
            $hasProps = false;
        } else {
            $hasProps = true;
            $this->output .= "\n" . count($props) . ' properties in properties file';
            $this->output .= "\nChecking: " . $element . ' and all included files';

            foreach ($props as $prop) {
                /* @var $prop xPDOObject */
                $name = $prop['name'];
                $names[] = $name;
            }

            foreach ($names as $name) {
                if (!in_array($name, array_values($this->codeMatches))) {
                    $orphans[] = $name;
                }
            }
        }
        if ($hasCodeProperties) {
            foreach($this->codeMatches as $key => $value) {
                if (! in_array($value, $names)) {
                    $missing[] = $value;
                }
            }
            if (!empty($missing)) {
                $this->output .= "\n    Missing from properties file";
                foreach ($missing as $missed) {
                    $this->output .= "\n        " . $missed;
                }
            } else {
                $this->output .= "\n    No properties missing from properties file:";
            }
        }
        if ($hasProps) {
            if (!empty($orphans)) {
                $this->output .= "\n    Properties in properties file not found in code:";
                foreach ($orphans as $orphan) {
                    $this->output .= "\n        " . $orphan;
                }
            } else {
                $this->output .= "\n    No unused properties found in properties file";
            }
        }

        if (!empty($missing)) {
            $pasteCode = $this->getPropertyCode($missing,$props);
            $this->output .= "\n\n******* Code to paste in properties file (will need editing) ********\n";
            $this->output .= $pasteCode;
        }
    }

    public function getPropertyCode($missing, $properties) {
        $prefix = $this->modx->getOption('prefix', $this->props, '');
        $packageNameLower = $this->props['packageNameLower'];
        $propertyTpl = "
        array(
            'name' => '[[+name]]',
            'desc' => '{$prefix}[[+name]]_desc~~(optional)Add description here for LexiconHelper',
            'type' => 'textfield',
            'options' => '',
            'value' => '',
            'lexicon' => '[[+package_name_lower]]:properties',
            'area' => '',
        ),
    ";
        $propertyText = '';
        if (empty($properties)) {
            $propertyText .= "\$properties = array (
    ";
        }

        foreach ($missing as $propertyName) {
            $tempPropertyTpl = str_replace('[[+name]]', $propertyName, $propertyTpl);
            $tempPropertyTpl = str_replace('[[+package_name_lower]]', $packageNameLower, $tempPropertyTpl);
            $propertyText .= $tempPropertyTpl;
        }

        if (empty($properties)) {
            $propertyText .= "
    );
    return \$properties;";
        }

        return $propertyText;
    }

    public function report() {
        echo $this->output;
    }
}
