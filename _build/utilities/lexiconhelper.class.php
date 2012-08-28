<?php
/**
 * lexiconhelper class file for MyComponent extra
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
 * methods used by lexiconhelper.php
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package mycomponent
 **/

class LexiconHelper {
    /* @var $modx modX - MODX object */
    public $modx;
    /* @var $props array  - $scriptProperties array */
    public $props;
    /* @var $helpers Helpers  - class of helper functions */
    public $helpers;
    public $packageName;
    public $packageNameLower;
    public $source;
    public $targetBase;
    public $targetCore;
    public $targetAssets;
    public $tplPath; /* path to element Tpl files */
    public $categoryId;
    public $dirPermission;
    public $filePermission;
    public $classFiles;
    public $included;
    public $output;
    public $codeMatches;
    public $loadedLexiconFiles;
    public $lexiconCodeStrings;

    function  __construct(&$modx, &$props = array()) {
        $this->modx =& $modx;
        $this->props =& $props;
    }

    public function init($configPath) {
        clearstatcache(); /*  make sure is_dir() is current */
        $config = $configPath;
        if (file_exists($config)) {
            $configProps = @include $config;
        }
        else {
            die('Could not find main config file at ' . $config);
        }

        if (empty($configProps)) {
            /* @var $configFile string - defined in included build.config.php */
            die('Could not find project config file at ' . $configFile);
        }
        $this->props = array_merge($configProps, $this->props);
        unset($config, $configFile, $configProps);
        echo 'Project: ' . $this->props['packageName']. "\n\n";
        $this->source = $this->props['source'];
        /* add trailing slash if missing */
        if (substr($this->source, -1) != "/") {
            $this->source .= "/";
        }
        require_once $this->source . '_build/utilities/helpers.class.php';
        $this->helpers = new Helpers($this->modx, $this->props);
        $this->helpers->init();

        $packageNameLower = $this->props['packageNameLower'];
        $this->targetBase = MODX_BASE_PATH . 'assets/mycomponents/' . $packageNameLower . '/';
        $this->targetCore = $this->targetBase . 'core/components/' . $packageNameLower . '/';
        clearstatcache(); /*  make sure is_dir() is current */
        $this->output = '';



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
        if (!empty($this->classFiles)) {
            $this->output .= "\nFound these class files: " . implode(', ', array_keys($this->classFiles));

        }

        foreach ($elements as $element => $type) {
            $this->included = array();
            $this->loadedLexiconFiles = array();
            $this->lexiconCodeStrings = array();
            $this->codeMatches = array();
            $this->getCode($element, $type);
            if (!empty($this->included)) {
                $this->output .= "\nFiles analyzed: " . implode(', ', $this->included);
            }
            $this->output .= "\nLexicon files: " . implode(', ', $this->loadedLexiconFiles);
            $this->output .= "\nLexicon strings: " . print_r($this->lexiconCodeStrings, true);
        }

        $this->report();
    }


    public function report() {
        echo $this->output;
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

        //$this->scriptCode = file_get_contents($file);
        $this->included[] = $element;
        $this->getIncludes($file);

    }

    /**
     * Searches for included .php files in code
     * and appends their content to $code reference var
     *
     * Also populates $this->lexiconCodeStrings and $this->loadedLexiconFiles
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
        }
        else {
            $this->output .= "\nCould not open file: " . $file;
            return;
        }
        $line = array();

        foreach ($lines as $line) {
            if (strstr($line,'lexicon->load')) {
                preg_match('#lexicon->load\s*\s*\(\s*\'(.*)\'#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (! in_array($matches[1], $this->loadedLexiconFiles )) {
                        $this->loadedLexiconFiles[] = $matches[1];
                    }
                }
                echo "\n" . $matches[1];

            } elseif (strstr($line, 'modx->lexicon')) {
                preg_match('#modx->lexicon\s*\s*\(\s*\'(.*)\'#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (strstr($matches[1], '~~' )) {
                        $s = explode('~~', $matches[1]);
                        $lexString = $s[0];
                        $value = $s[1];
                    } else {
                        $lexString = $matches[1];
                        $value = '';
                    }
                    if (!in_array($lexString, array_keys($this->lexiconCodeStrings))) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                        // $this->lexiconCodeStrings[] = $matches[1];
                    } elseif (empty($this->lexiconCodeStrings[$lexString]) && !empty($value)) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    }
                }
                echo "\n" . $matches[1];

            }

            if (strstr($line, 'include') || strstr($line, 'include_once') || strstr($line, 'require') || strstr($line, 'require_once')) {
                //$this->output .= "\nHIT: " . $line;

                preg_match('#[0-9a-zA-Z_\-\s]*\.class\.php#', $line, $matches);
                //$this->output .= "\n" . $matches[0];


                if (isset($this->classFiles[$matches[0]])) {

                    //$this->output .= "\nIn Classfiles Array";
                    // skip files we've already included
                    if (!in_array($matches[0], $this->included)) {
                        //$this->output .= "\n\nRecursing";
                        //$this->scriptCode .= file_get_contents($this->classFiles[$matches[0]] . '/' . $matches[0]);
                        $this->included[] = $matches[0];
                        $this->getIncludes($this->classFiles[$matches[0]] . '/' . $matches[0]);
                    }
                }
            }
        }
    }

    public function dir_walk($callback, $dir, $types = null, $recursive = false, $baseDir = '') {

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
                }
                elseif ($recursive && is_dir($dir . '/' . $file)) {
                    $this->dir_walk($callback, $dir . '/' . $file, $types, $recursive, $baseDir . '/' . $file);
                }
            }
            closedir($dh);
        }
    }


}
