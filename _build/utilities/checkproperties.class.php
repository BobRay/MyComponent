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



    function __construct(&$props = array()) {
        $this->props =& $props;
    }

    public function init($configPath) {
        /** @var $modx modX */
        if (!defined('MODX_CORE_PATH')) {

            require_once dirname(dirname(__FILE__)) . '/build.config.php';
            require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
            /*$modx = new modX();
            $modx->initialize('mgr');
            $modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $modx->setLogTarget('ECHO');*/
        }
        if (!php_sapi_name() == 'cli') {
            $this->output .= "<pre>\n"; /* used for nice formatting for log messages  */
        }


        $config = $configPath;
        if (file_exists($config)) {
            $configProps = @include $config;
        } else {
            die('Could not find main config file at ' . $config);
        }

        if (empty($configProps)) {
            /* @var $configFile string - defined in included build.config.php */
            die('Could not find project config file at ' . $configFile);
        }
        $this->props = array_merge($configProps, $this->props);
        unset($config, $configFile, $configProps);

        $packageNameLower = $this->props['packageNameLower'];
        $this->targetBase = MODX_BASE_PATH . 'assets/mycomponents/' . $packageNameLower . '/';
        $this->targetCore = $this->targetBase . 'core/components/' . $packageNameLower . '/';
        clearstatcache(); /*  make sure is_dir() is current */
        $aliases = $this->props['scriptPropertiesAliases'];

        $aliases = explode(',', $aliases);
        $this->spAliases = array();
        foreach ($aliases as $alias) {
            $this->spAliases[] = '\$' . $alias;
            $this->spAliases[] = '\$' . 'this->' . $alias;
        }

        $this->included = array();
        $this->output = '';

        /*require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/bootstrap.class.php';
        $this->bootStrap = new Bootstrap($modx, $props); */

    }
    public function run() {
        $snippets = $this->props['elements']['modSnippet'];
        $elements = array();
        /* get all plugins and snippets from config file */
        foreach (explode(',', $snippets) as $snippet) {
            $elements[strtolower(trim($snippet))] = 'modSnippet';
        }
        $plugins = $this->props['elements']['modPlugin'];
        foreach (explode(',', $plugins) as $plugin) {
            $elements[strtolower(trim($plugin))] = 'modPlugin';
        }
        $this->classFiles = array();
        $x = 'addClassFiles';
        $dir = $this->targetCore . 'model';
        $this->dir_walk($x, $dir, null, true);
        if(!empty($this->classFiles)) {
            $this->output .= "\nFound these class files: " . implode(', ', array_keys($this->classFiles));

        }

        // $this->getCode('plugin1', 'modPlugin');
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
    public function addClassFiles($dir, $file) {
        //$this->output .= "\nIn addClassFiles";
        $this->classFiles[$file] = $dir;
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
        $this->output .= "\nProcessing Element: " . $element . " -- Type: " . $type;
        //$code = file_get_contents($file);
        //$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        //$this->output .= $code;

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
        //preg_match_all('~<[?].*?(?:include|require(?:_once)?)\s *?(?:[(] ?[\'"])(.+?)(?:[\'"][)]?)\s*?;.*?(?:[?]>)?~is', $code, $matches);
        /*for ($i = 258; $i <= 380; $i++) {
            $this->output .= "\n" . $i . ' - ' . token_name($i);
        }*/
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
        $line = array();

        foreach ($lines as $line) {

            if (strstr($line, 'include') || strstr($line, 'include_once') || strstr($line, 'require') || strstr($line, 'require_once')) {
                //$this->output .= "\nHIT: " . $line;

                preg_match('#[0-9a-zA-Z_\-\s]*\.class\.php#',$line, $matches);
                //$this->output .= "\n" . $matches[0];

                if (isset($this->classFiles[$matches[0]])) {

                    //$this->output .= "\nIn Classfiles Array";
                    /* skip files we've already includes */
                    if (! in_array($matches[0], $this->included)) {
                        //$this->output .= "\n\nRecursing";
                        /* file_get_contents add to code goes here */
                        $this->scriptCode .= file_get_contents($this->classFiles[$matches[0]] . '/' . $matches[0]);
                        $this->included[] = $matches[0];
                        $this->getIncludes($this->classFiles[$matches[0]] . '/' . $matches[0]);
                    }
                }
            }
        }
    }
    public function getProperties() {
        //$propsAlias = '$this->props';
        /* escape the $ */
        foreach($this->spAliases as $key => $alias) {
            //$propsAlias = str_replace('$', '\$', $propsAlias);
            $matches = array();

    //$pattern = "/getOption\(\'([^\\']+)\'.+" . $propsAlias . "/";
            $pattern = "/" . $alias . "\[[\"\']([^\"\']+)/";
    //$this->output .= 'PATTERN: ' . $pattern . "\n";
            /* get properties used with $scriptProperties['propertyName'] */
            preg_match_all($pattern, $this->scriptCode, $matches);
    //$this->output .= "\nSp-alias\n" . print_r($matches[1], true);
            $codeMatches = $matches[1];

            /* get properties used with plain old $scriptProperties */
           /* if ($propsAlias != "\$scriptProperties") {
                $matches = array();
                $pattern = "/" . "scriptProperties\[[\"\']([^\"\']+)/";
                preg_match_all($pattern, $this->scriptCode, $matches);

                // $this->output .= "\nSCRIPRPROPERTIES\n" . print_r($matches[1], true);

                $codeMatches = array_merge($codeMatches, $matches[1]);
            }*/
            $matches = array();
            /* get properties accessed with getOption() */
            $pattern = "/getOption\(\'([^\']+)'.+" . $alias . "/";
            preg_match_all($pattern, $this->scriptCode, $matches);
    //$this->output .= "\n PropsAlias " . $propsAlias . "\n";
    //$this->output .= "\n getOption\n" . print_r($matches[1], true);
            $codeMatches = array_merge($codeMatches, $matches[1]);

            //$codeMatches = array_unique($this->codeMatches);

            //$this->output .= "\nCOUNT: " . count($codeMatches) . " properties in code file(s)\n";
            $this->codeMatches = array_merge($this->codeMatches, $codeMatches);
        }
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
            $hasPropsFile = true;

        } else {
            $this->output .= "\nNo Properties file for " . $element . ' ' . $type;
            $this->output .= "\nLooked for: " . $propsFile;
            $hasPropsFile = false;
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
                    //$this->output .= "\n    ORPHAN: " . $name;
                }
            }
        }
        if ($hasCodeProperties) {
            foreach($this->codeMatches as $key => $value) {
                if (! in_array($value, $names)) {
                    $missing[] = $value;
                    //$this->output .= "\n    MISSING: " . $value;
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
        $prefix = $this->props['prefix'];
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

    public function dir_walk($callback, $dir, $types = null, $recursive = false, $baseDir = '')
    {

        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                // $this->output .= "\n" , $dir;
                //$this->output .= "\n", $file;
                if (is_file($dir . '/' . $file)) {
                    if (is_array($types)) {
                        if (!in_array(strtolower(pathinfo($dir . $file, PATHINFO_EXTENSION)), $types, true)) {
                            continue;
                        }
                    }
                    $this->{$callback}($dir, $file);
                } elseif ($recursive && is_dir($dir . '/' . $file)) {
                    $this->dir_walk($callback, $dir . '/' . $file, $types, $recursive, $baseDir . '/' . $file);
                }
            }
            closedir($dh);
        }
    }


}
