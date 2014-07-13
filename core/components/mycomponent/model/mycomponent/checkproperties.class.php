<?php
/**
 * checkproperties script file for MyComponent extra
 *
 * Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
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
            session_write_close();
            die('Could not find current.project.php file at: ' . $currentProjectPath);
        }

        $projectConfigPath = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/') .
            '_build/config/' . strtoLower($currentProject) . '.config.php';

        if (file_exists($projectConfigPath)) {
            $properties = include $projectConfigPath;
        } else {
            session_write_close();
            die('Could not find Project Config file at: ' . $projectConfigPath);
        }

        /* Make sure that we get usable values */
        if (!is_array($properties) or empty($properties)) {
            session_write_close();
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

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_project')
            . ': ' . $this->helpers->getProp('packageName'));

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
            . ': ' .
                $this->modx->lexicon('mc_check_properties')
         . "\n");
        $this->source = $this->helpers->getProp('mycomponentRoot');
        /* add trailing slash if missing */
        if (substr($this->source, -1) != "/") {
            $this->source .= "/";
        }


        $this->packageNameLower = $this->helpers->getProp('packageNameLower');
        $this->targetBase = $this->helpers->getProp('targetRoot');
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';

        clearstatcache(); /*  make sure is_dir() is current */


        $aliases = $this->helpers->getProp('scriptPropertiesAliases', array());

        $this->spAliases = array();
        foreach ($aliases as $alias) {
            $this->spAliases[] = '\$' . $alias;
            $this->spAliases[] = '\$' . 'this->' . $alias;
        }

        $this->included = array();
        $this->output = '';
    }
    public function run() {
        $snippets = $this->modx->getOption('snippets', $this->helpers->getProp('elements', array()), array());
        $elements = array();
        /* get all plugins and snippets from config file */
        foreach ($snippets as $snippet => $fields) {
            $type = 'modSnippet';
            if (isset($fields['name'])) {
                $snippet = $fields['name'];
            }
            if (isset($fields['filename'])) {
                $type .= ':' . $fields['filename'];
            }
            $elements[strtolower(trim($snippet))] = $type;
        }
        $plugins = $this->modx->getOption('plugins',$this->helpers->getProp('elements', array()), array());
        foreach ($plugins as $plugin => $fields) {
            $type = 'modPlugin';
            if (isset($fields['name'])) {
                $plugin = $fields['name'];
            }
            if (isset($fields['filename'])) {
                $type .= ':' . $fields['filename'];
            }
            $elements[strtolower(trim($plugin))] = $type;
        }
        $this->classFiles = array();
        $dir = $this->targetCore . 'model';
        if (is_dir($dir)) {
            $this->helpers->dirWalk($dir, null, true);
            $this->classFiles = $this->helpers->getFiles();
        }
        $this->helpers->resetFiles();
        $dir = $this->targetCore . 'processors';
        if (is_dir($dir)) {
            $this->helpers->dirWalk($dir, NULL, true);
            $this->classFiles = array_merge($this->helpers->getFiles(), $this->classFiles);
        }

        if(!empty($this->classFiles)) {
            $this->output .= "\nFound these class files: " . implode(', ', array_keys($this->classFiles));
        }

        /* process each element */
        foreach($elements as $element => $type) {
            $fileName = '';
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
            $this->codeMatches = array_unique($this->codeMatches);
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
        $fileName = '';
        if (strpos($type, ':') !== false) {
            $couple = explode(':', $type);
            $type = $couple[0];
            $fileName = $couple[1];
        }
        if (empty($element)) {
            $this->output .= 'Error: Element is empty';
            return;
        }
        $typeName = strtolower(substr($type, 3));
        $dir = $this->targetCore . 'elements/' . $typeName . 's/';
        if (empty($fileName)) {
            $fileName = $element . '.' . $typeName . '.php';
            $file = $dir . $element . '.' . $typeName . '.php';
        } else {
            $file = $dir . $fileName;
        }
        $this->output .= "\n\n*********************************************";
        $this->output .= "\n" . 'Processing Element: ' . $fileName . " -- Type: " . $type;
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
        if (strpos($elementType,':') !== false) {
            $couple = explode(':', $elementType);
            $elementType = $couple[0];
        }
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
                if (! in_array($value, array_keys($props))) {
                    if (! in_array($value, $missing)) {
                        $missing[] = $value;
                    }
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
        $packageNameLower = $this->helpers->getProp('packageNameLower');
        $propertyTpl = "
        '[[+name]]' => array(
            'name' => '[[+name]]',
            'desc' => '{$prefix}[[+name]]_desc',
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
        // echo $this->output;
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, $this->output);
    }
}
