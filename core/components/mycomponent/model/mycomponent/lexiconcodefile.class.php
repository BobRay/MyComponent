<?php

/**
 * LexiconFile class file for MyComponent extra
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
 *
 * Methods for parsing lexicon strings in code files.
 * Handles .php, .js, and chunk, template, and resource files,
 * but not properties files.
 *
 * @package mycomponent
 **/


class LexiconCodeFile {
    public $missing = array();
    public $empty = array();
    public $lexDir = '';
    public $lexFileName;
    public $lexFiles = array();
    public $errors = array();
    /** @var array $content - array of lines from this code file */
    public $content = array();
    /** @var int $updateCount - count of strings that have been
     * updated in lexicon file  */
    public $updateCount = 0;
    /** @var $modx modX */
    public $modx = null;
    /** @var helpers Helpers */
    public $helpers = null;
    public $language = '';

    /** @var string $code - code from file and all included files */
    public $code = '';
    /** @var array $used - $_lang array from all used lex files */
    public $used = array();
    /** @var array $defined - lex strings used in this file */
    public $defined = array();

    /** @var array $toUpdate - lex entries that have been updated */
    public $toUpdate = array();


    /**
     * @param $modx modX - $modx object
     * @param $helpers Helpers - $helpers class
     * @param $path string - path to code file
     * @param $fileName string - file name of code file
     * @param $lexDir string - path to lexicon directory (e.g. lexicon/en)
     */
    function __construct(&$modx, $helpers, $path, $fileName, $lexDir) {
        $this->modx =& $modx;
        $this->helpers = $helpers;
        $this->path = rtrim($path, '/\\');
        $this->fileName = $fileName;
        // $this->addCodeFile($path, $fileName);
        $this->setLanguage();
        $this->lexDir = rtrim($lexDir, '/\\');
        $this->lexDir = strtolower(str_replace('\\','/', $this->lexDir));
        $this->setContent();
        $this->setLexfiles();
        $this->setUsed();
        $this->setDefined();
        $this->setMissing();
    }

    /* Getters */

    public function  getFileName() {
        return $this->fileName;

    }
    public function getUsed() {
        return $this->used;
    }

    public function getMissing() {
        return $this->missing;
    }

    public function getEmpty() {
        return $this->empty;
    }

    public function getDefined() {
        return $this->defined;
    }

    public function getLexFiles() {
        return $this->lexFiles;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getUpdateCount() {
        return $this->updateCount;
    }

    public function getToUpdate() {
        return $this->toUpdate;
    }

    /* Setters */
    public function setLanguage($language = '') {
        if (! empty ($language)) {
            $this->language = $language;
        } else {
            $languages = $this->modx->getOption('languages', $this->helpers->props, array());
            $language = key($languages);
            $this->language = empty($language)
                ? 'en'
                : $language;
        }

    }

    public function setDefined($defined = array()) {
        if (!empty($defined)) {
            $this->$defined = $defined;
        } else {
            foreach ($this->lexFiles as $fullPath => $fileName) {
                if (file_exists($fullPath)) {
                    include $fullPath;
                }
            }
            /* @var $_lang array */
            if (isset($_lang)) {
                $this->defined = $_lang;
            }
        }
    }

    public function setLexfiles($fileName = '') {
        if (empty($fileName)) {
            /* find lexicon->load lines in file */
            $lines = $this->content;
            foreach($lines as $line) {
                if (strstr($line, 'lexicon->load')) {
                    $matches = array();
                    preg_match('#lexicon->load\s*\s*\(\s*\'(.*)\'#', $line, $matches);
                    if (isset($matches[1]) && !empty($matches[1])) {
                        /* skip dynamic lex loads */
                        if (strpos($matches[1], '$')) {
                            continue;
                        }
                        // $fqn = $this->getLexFqn($matches[1]);
                        $this->addLexFile($matches[1]);
                    }
                }
            }
        } else {
            $this->lexFileName = $fileName;
            $this->addLexfile($this->getLexFqn($fileName));
        }
        /* assume default.inc.php if no lex files specified */
        if (empty($this->lexFiles)) {
            $this->addLexFile('default');
        }
    }

    public function setContent($content = '') {
        if (empty($content)) {
            $fullPath = $this->path . '/' . $this->fileName;
            $content = file_get_contents($fullPath);
        }
        $this->content = explode("\n", $content);
    }

    public function setUsed($used = array()) {
        if (!empty($used)) {
            $this->used = $used;
        } else {
            $this->used = array();
            if (strpos($this->fileName, '.php') !== false) {
                $type = 'php';
                $pattern = '#modx->lexicon\s*\s*\(\s*[\'\"]([^\)]*)[\'\"]#';
            } elseif (strpos($this->fileName, '.js') !== false) {
                $type = 'js';
                $pattern = '#_\(\s*[\'\"](.*)[\'\"]\)#';
            } else {
                $type = 'text';
                $pattern = '#\[\[!*%([^\?&\]]*)#';
            }

            /* Iterate over lines to rind lexicon strings
               in code file */

            $lines = $this->content;
            foreach ($lines as $line) {
                if (($type == 'php') && (!strpos($line, 'modx->lexicon'))) {
                    continue;
                }
                if (($type == 'js') && (!strpos($line, '_('))) {
                    continue;
                }
                if ($type == 'text') {
                    if (($type == 'js') && (!strpos($line, '[['))) {
                        continue;
                    }
                }
                $matches = array();
                preg_match($pattern, $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (strstr($matches[1], '~~')) {
                        $s = explode('~~', $matches[1]);
                        $lexString = $s[0];
                        $value = $s[1];
                    } else {
                        $lexString = $matches[1];
                        $value = '';
                    }

                    $this->used[$lexString] = $value;
                }
            }
        }
    }

    public function setMissing($missing = array()) {
        if (empty($missing)) {
            foreach($this->used as $key => $value) {
                if (! array_key_exists($key, $this->defined)) {
                    /* missing keys */
                    $this->missing[$key] = $value;
                } elseif (($this->defined[$key] !== $value)
                    && (!empty($value))) {
                    /* Updated keys */
                    $this->toUpdate[$key] = $value;
                }
            }

        } else {
            $this->missing = $missing;
        }
    }

    public function setError($message) {
        $this->errors[] = $message;
    }

    public function hasError() {
        return !empty($this->errors);
    }

    /*public function addCodeFile($path, $fileName) {
        $path = rtrim($path, '/\\');
        $path .= '/' . $fileName;
        if (!array_key_exists($path, $this->codeFiles)) {
            $this->codeFiles[$path] = $fileName;
        }

    }*/


    public function addLexFile($fqn) {
        $fqn = $this->getLexFqn($fqn);
        $val = explode(':', $fqn);
        $fileName = $val[2] . '.inc.php';
        $fullPath = $this->lexDir . '/' . $val[0] . '/' . $fileName;

        if (! array_key_exists($fullPath, $this->lexFiles)) {
            $this->lexFiles[$fullPath]  = $fileName;
        }
    }

    /**
     * Returns a fully qualified lexicon spec (e.g. 'example:en:default.inc.php')
     * @param $lexFileSpec (partial or full lexicon spec (e.g., default, en:default)
     * @return string
     */
    public function getLexFqn($lexFileSpec) {
        $nspos = strpos($lexFileSpec, ':');
        $language = $this->language;


        $namespace = $this->helpers->getProp('packageNameLower');
        if ($nspos === false) {
            $topic_parsed = $lexFileSpec;

        } else { /* if namespace, search specified lexicon */
            $params = explode(':', $lexFileSpec);
            if (count($params) <= 2) {
                $namespace = $params[0];
                $topic_parsed = $params[1];
            } else {
                $language = $params[0];
                $namespace = $params[1];
                $topic_parsed = $params[2];
            }
        }
        return $language . ':' . $namespace . ':' . $topic_parsed;
    }

    public function updateLexiconFile() {
        if (empty($this->missing) && empty($this->toUpdate)) {
            /* Nothing to do */
            return;
        }
        /* This should never happen */
        if (count($this->lexFiles) !== 1) {
            $this->setError('multiple lexfiles');
            return;
        }

        reset($this->lexFiles);
        $path = key($this->lexFiles);
        if (! file_exists($path))  {
            $this->setError('LexFile not found');
            return;
        }
        $content = file_get_contents($path);

        /* Add new strings */
        if (!empty($this->missing)) {
            $code = '';
            foreach($this->missing as $key => $value) {
                $code .= "\n\$_lang['" . $key . "'] = " . var_export($value, true) . ';';
            }

            $comment = $comment = '/* used in ' . $this->fileName . ' */';
            if (strstr($content, $comment)) {
                $content = str_replace($comment, $comment . $code, $content);
                $fp = fopen($path, 'w');
                if ($fp) {
                    fwrite($fp, $content);
                    fclose($fp);
                    $success = true;
                }
            } else {
                $fp = fopen($path, 'a');
                if ($fp) {
                    fwrite($fp, "\n\n" . $comment . $code);
                    fclose($fp);
                    $success = true;
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_could_not_open_lex_file')
                        . ': ' . $path);

                }
            }
        }

        /* Update Changed strings */

        /* This may have changed */

        if (!empty($this->toUpdate)) {
            $content = file_get_contents($path);

            foreach($this->toUpdate as $key => $value) {
                $pattern = '#\$_lang\[["\']' .
                    $key . '[^=]+=\s([\'"][^\'"]*[\'"])#';
                preg_match($pattern, $content, $matches);
                if (isset($matches[1])) {
                    $replace = str_replace($matches[1], var_export($value,true),
                        $matches[0]);
                    $content = str_replace($matches[0], $replace, $content);
                }
            }
            $fp = fopen($path, 'w');
            if ($fp) {
                fwrite($fp, $content);
                fclose($fp);
            }
        }
    }

    public function updateCodeFile() {

    }

}
