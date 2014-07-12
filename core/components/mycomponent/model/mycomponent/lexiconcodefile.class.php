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

class LexiconCodeFileFactory {

    /**
     * prevent this class being instantiated
     */
    private function __construct() {
    }

    /**
     * Returns the appropriate LexiconCodeFile object based on $fileName;
     * params are passed through to the object class constructor
     *
     * @param $modx modX
     * @param $helpers Helpers
     * @param $path string - Path to code file
     * @param $fileName string - File name of code file
     * @param $lexDir -Base lexicon directory (e.g. /lexicon/
     *
     * @return LexiconCodeFile - object created by factory
     */
    public static function getInstance(&$modx, $helpers, $path, $fileName, $lexDir) {
        if (strpos($fileName, '.menus.php') !== false) {
            $type = 'Menu';
        } elseif (strpos($fileName, '.settings.php') !== false) {
            $type = 'Settings';
        } elseif (strpos($fileName, 'properties.') === 0) {
            $type = 'Properties';
        } elseif (strpos($fileName, '.php') !== false) {
            $type = 'Php';
        } elseif (strpos($fileName, '.js') !== false) {
            $type = 'Js';
        } else {
            $type = 'Text';
        }
        $className = $type . 'LexiconCodeFile';
        if ($type == 'Properties' || $type == 'Settings' || $type == 'Menu') {
            $fileObj =  new $className($modx, $helpers, $path, $fileName, $lexDir);

        } else {
            $fileObj = new LexiconCodeFile($modx, $helpers, $path, $fileName, $lexDir);
        }
        $fileObj->type = $type;
        $fileObj->init();
        return $fileObj;
    }
}

/**
 * Base class for LexiconCodeFile objects.
 * Includes methods shared by all code file objects
 *
 * @param $modx modX - $modx object
 * @param $helpers Helpers - $helpers class
 * @param $path string - path to code file
 * @param $fileName string - file name of code file
 * @param $lexDir string - path to lexicon directory (e.g. lexicon/)
 */

abstract class AbstractLexiconCodeFile {
    /**
     * @var $missing array - array of strings used in code
     * but missing from lex file
     */
    public $missing = array();
    /**

    /**
     * @var $lexdir string - directory of lexicon topic
     * file for this code file
     */
    public $lexDir = '';
    /**
     * @var $lexFileName string - name of lexicon topic file
     */
    public $lexFileName;
    /**
     * @var $lexFiles array - array of lex file strings in the form:
     * fileName => fullPath
     */
    public $lexFiles = array();
    /**
     * @var array
     */
    public $errors = array();
    /** @var array $content - array of lines from this code file */
    public $content = array();

    /** @var  $content string - raw content of code file */
    public $rawContent;

    /** @var int $updateCount - count of strings that have been
     * updated in lexicon file  */
    public $updateCount = 0;
    /** @var $modx modX */

    public $modx = null;
    /** @var helpers Helpers */

    public $helpers = null;
    /** @var $language string - */
    public $language = '';

    /** @var string $code - code from file and all included files */
    public $code = '';

    /** @var array $used - lex strings used in this file */
    public $used = array();

    /** @var array $defined - $_lang array with all strings defined in
     *  all specified lexicon topic files */
    public $defined = array();

    /** @var array $toUpdate - lex entries that don't match those
     * in the lex file and need to be updated */
    public $toUpdate = array();

    /** @var int $squigglesFound - count of squiggles
     * tokens (~~) found */
    public $squigglesFound = 0;

    /** @var $type string - type of the code file for this object
     * (Php, Text, JS, Properties, Menu, Settings) */
    public $type = '';

    /** @var $pattern string - regex pattern for lex strings
     * in file of this type */
    public $pattern = '';

    /** @var $subPattern string - string identifying lines with lex
     * strings type in files of this type (other lines are skipped) */
    public $subPattern = '';


    /**
     * Children have no constructor so this will always be called
     *
     * @param $modx modX
     * @param $helpers Helpers
     * @param $path string - full directory of code file
     * @param $fileName string - file name of code file
     * @param $lexDir string - full path to base lexicon directory (lexicon/)
     */
    function __construct(&$modx, $helpers, $path, $fileName, $lexDir) {

        $this->modx =& $modx;
        $this->helpers = $helpers;
        $this->path = rtrim($path, '/\\');
        $this->fileName = $fileName;
        $this->_setLanguage();
        $this->lexDir = rtrim($lexDir, '/\\');
        $this->lexDir = strtolower(str_replace('\\', '/', $this->lexDir));
    }

    /* These two must be implemented in child classes */
    abstract public function _setLexFiles();

    abstract public function _setUsed();


    /**
     * Fully initialize Code File object. Does everything
     * but update lexicon and code files
     */
    public function init() {

        /* Set pattern and subPattern used to search for lex strings
         * in code file
         */
        switch ($this->type) {
            case 'Menu':
                $this->pattern = '#[\'\"]description[\'\"]\s*=>\s*(\'|\")(.*)\1#';
                $this->subPattern = 'description';
                break;
            case 'Php':
                /* ToDo: Try this -- may prevent need to separate lex lines */
                // $this->pattern = '#modx->lexicon\s * \(\s * (\'|\")(.+?)\1\)#';

                /*$this->pattern = '#modx->lexicon\s*\(\s*(\'|\")(.*)\1\)#';
                $this->subPattern = 'modx->lexicon';*/
                $this->pattern = '#(?:modx|xpdo)->lexicon\s*\(\s*(\'|\")(.*)\1\)#';
                $this->subPattern = '->lexicon';
                break;
            case 'Js':
                $this->pattern = '#_\(\s*(\'|\")(.*)\1\)#';
                $this->subPattern = '_(';
                break;
            case 'Text':
                $this->pattern = '#(\[\[)!*%([^\?&\]]*)#';
                $this->subPattern = '[[';
                break;
            case 'Properties':
                break;
            case 'Settings':
                break;
            default:
                $msg = $this->modx->lexicon('mc_unknown_file_type');
                $this->_setError($msg);

        }
        $this->_setContent();
        $this->_setLexFiles();
        $this->_setUsed();
        $this->_setDefined();
        $this->_setMissing();
    }

    /**
     * Return true if an error is set, false if not
     *
     * @return bool
     */
    public function hasError() {
        return !empty($this->errors);
    }

    /**
     * Return the array of error messages set here
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }


    /**
     * Return the two-letter primary language code extracted
     * from the languages array in the project config file
     *
     * @param string $language
     */
    public function _setLanguage($language = '') {
        if (!empty ($language)) {
            $this->language = $language;
        } else {
            $languages = $this->modx->getOption('languages', $this->helpers->props, array());
            $language = key($languages);
            $this->language = empty($language)
                ? 'en'
                : $language;
        }

    }

    /**
     * Create the array of lines from the code file in $this->content
     *
     * @param string $content - (optional) array of content lines
     */
    public function _setContent($content = '') {
        if (empty($content)) {
            $fullPath = $this->path . '/' . $this->fileName;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
            } else {
                $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
            }
        }
        $this->rawContent = $content;
        $this->content = explode("\n", $content);

    }

    /**
     * Add an error message to the $this->errors array
     *
     * @param $message string - message to add
     */
    public function _setError($message) {
        $this->errors[] = $message;
    }

    /**
     * Find lex strings in code file that are not in the Lexicon file
     * and add them to $this->missing array.
     *
     * @param array $missing - (optional) array of missing strings.
     */
    public function _setMissing($missing = array()) {
        if (empty($missing)) {
            foreach ($this->used as $key => $value) {
                if (!array_key_exists($key, $this->defined)) {
                    /* missing keys */
                    $this->missing[$key] = $value;
                } elseif (($this->defined[$key] !== $value)
                    && (!empty($value))
                ) {
                    /* Updated keys */
                    $this->toUpdate[$key] = $value;
                }
            }
        } else {
            $this->missing = $missing;
        }
    }

    /**
     * Add a lexicon topic file to $this->lexFiles in the form:
     * fileName => fullPath
     *
     * @param $topic string - can be a topic or a fully or partially qualified lex file spec.
     */
    public function addLexFile($topic) {
        $fqn = $this->getLexFqn($topic);
        $val = explode(':', $fqn);
        $fileName = $val[2] . '.inc.php';
        $fullPath = $this->lexDir . '/' . $val[0] . '/' . $fileName;

        if (!array_key_exists($fileName, $this->lexFiles)) {
            $this->lexFiles[$fileName] = $fullPath;
        }
    }

    /**
     * Return a fully qualified lexicon spec (e.g. 'example:en:default.inc.php')
     *
     * @param $lexFileSpec (partial or full lexicon spec. (e.g., default, en:default)
     * @return string - fully qualified lex spec. (e.g. en:example:default)
     */
    public function getLexFqn($lexFileSpec) {
        $nspos = strpos($lexFileSpec, ':');
        $language = empty($this->language)? 'en' : $this->language;


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
        $topic_parsed = empty($topic_parsed)? 'default' : $topic_parsed;
        return $language . ':' . $namespace . ':' . $topic_parsed;
    }

    /**
     * Create the array of lexicon strings in lexicon files used by this code file in the form:
     * key => value
     */
    public function _setDefined($defined = array()) {
        if (!empty($defined)) {
            $this->$defined = $this->defined + $defined;
        } else {
            foreach ($this->lexFiles as $fileName => $fullPath) {
                if (file_exists($fullPath)) {
                    include $fullPath;
                }
            }
            /* @var $_lang array */
            if (isset($_lang)) {
                $this->defined = $this->defined + $_lang;
            }
        }
    }

    /**
     * Update a code file by removing the ~~* part of the lexicon strings.
     *
     * @return int number of ~~ strings removed
     */
    public function updateCodeFile() {
        if (empty($this->squigglesFound)) {
            return 0;
        }
        $fileName = $this->fileName;
        $fullPath = $this->path . '/' . $fileName;
        $content = file_get_contents($fullPath);

        $type = (strpos($fileName, '.php') !== false) || (strpos($fileName, '.js') !== false)
            ? 'modScript'
            : 'text';



        /* Need to handle trailing quote in scripts.
           Files with tags have no trailing quote */
        if (strpos($content, '~~') !== false) {
            /* Protect naked '~~' */
            $naked = false;
            if (strpos($content, "'~~") !== false) {
                $content = str_replace("'~~'", "'sqsq'", $content);
                $naked = true;
            }
            /* .php and .js files */
            if ($type == 'modScript') {
                $pattern = '/~~.*([\'\"][\),])/';
                $replace = '$1';
            } else {
                /* text files */
                $pattern = '/~~[^\]\?&]+/';
                $replace = '';
            }

            $content = preg_replace($pattern, $replace, $content);

            if ($naked) {
                /* Restore naked '~~' */
                $content = str_replace("'sqsq'", "'~~'", $content);
            }
            if (!empty($content)) {
                $fp = fopen($fullPath, 'w');
                if ($fp) {
                    fwrite($fp, $content);
                    fclose($fp);
                }

            }
        }

        return $this->squigglesFound;
    }

    /**
     *  Create/Update the strings in the selected lexicon topic file
     */
    public function updateLexiconFile() {
        if (empty($this->missing) && empty($this->toUpdate)) {
            /* Nothing to do */
            return;
        }
        /* This should never happen */
        if (count($this->lexFiles) !== 1) {
            $this->_setError($this->modx->lexicon('mc_cannot_update_multiple_lex_files'));
            return;
        }

        $path = reset($this->lexFiles);
        if (!file_exists($path)) {
            $this->_setError('LexFile not found');
            return;
        }
        $content = file_get_contents($path);

        /* Add new strings */
        if (!empty($this->missing)) {
            $code = '';
            foreach ($this->missing as $key => $value) {
                $key = var_export($key, true);
                $value = var_export($value, true);
                $value = str_replace("\\\\\\", '\\', $value);
                $value = str_replace("\\\\", '', $value);
                $code .= "\n\$_lang[$key] = " . $value . ';';
            }
            $success = false;
            $comment = $comment = '/* Used in ' . $this->fileName . ' */';
            if (stristr($content, $comment)) {
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
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_could_not_open_lex_file')
                        . ': ' . $path);

                }
            }
            if (!$success) {
                $this->_setError($this->modx->lexicon('mc_error_writing_lexicon_file') .
                ': ' . $path);
            }
        }

        /* Update Changed strings */
        if (!empty($this->toUpdate)) {
            /* This may have changed */
            $content = file_get_contents($path);

            foreach ($this->toUpdate as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $pattern = '#\$_lang\[[\"\']' . $key . '[^=]+=\s*([^;]+);#';
                preg_match($pattern, $content, $matches);

                if (isset($matches[1])) {
                    $value = var_export($value, true);
                    $value = str_replace('\\\\\\', '\\', $value);
                    $value = str_replace("\\\\", '', $value);
                    $replace = str_replace($matches[1], $value,
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
}


/**
 * Class LexiconCodeFile
 *
 * Handles Php, Js, and Text files
 */
class LexiconCodeFile extends AbstractLexiconCodeFile {

    /**
     * Set the lexicon topic for the file and add it to the $this->lexFiles array
     */
    public function _setLexFiles() {
        $isMenuFile = strpos($this->fileName, 'menus.php') !== false;

        /* set default $pattern and $subPattern */
        $subPattern = 'lexicon->load';
        $pattern = '#lexicon->load\s*\s*\(\s*\'(.*)\'#';

        $default = 'default';
        /* find lexicon->load lines in file or other lex file specification */
        $lines = $this->content;

        /* These have lex topic specified in their fields */
        if ($isMenuFile) {
            $subPattern = 'lang_topics';
            $pattern = '#^\s*[\"\']lang_topics[\'\"]\s*=>\s*[\"\'](.*)[\'\"]#';
        }
        /* handle controller class files */
        if (strpos($this->fileName,'class.php') !== false) {
            $p = '#function getLanguageTopics\(\)\s*\{\s*return\s*array\([\'\"]([^\"\']+)[\"\']\)#';
            $matches = array();
            preg_match($p, $this->rawContent, $matches);
            if (isset($matches['1'])) {
                $topics = explode(',', $matches[1]);
                foreach ($topics as $topic) {
                    $this->addLexFile($topic);
                }
            }
        }

        /* iterate over lines to find lexicon topic specification */
        foreach($lines as $line) {
            /* skip lines without subPattern */
            if (strstr($line, $subPattern)) {
                $matches = array();
                preg_match($pattern, $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {

                    /* skip dynamic lex loads */
                    if (strpos($matches[1], '$') !== false) {
                        continue;
                    }

                    /* skip references to core lexicon files */
                    if (strpos($matches[1], 'core:') !== false) {
                        continue;
                    }

                    if ($isMenuFile) {
                        if ($matches[1] == $this->helpers->props['packageNameLower']) {
                            /* Correct if just the package name */
                            $matches[1] = $matches[1] . ':' . $default;
                        }
                        $this->addLexFile($matches[1]);
                        /* bail out at the first non-empty lexicon specification */
                        break;
                    }
                    $this->addLexFile($matches[1]);
                }
            }
        }

        /* assume 'default' topic if no topic specified */
        if (empty($this->lexFiles)) {
            $this->addLexFile($default);
        }
    }


    /**
     * Find all lexicon strings and their values (if any) in the code file
     * and add them to $this->used array.
     */
    public function _setUsed() {
        /* skip minified JS files */
        if (strstr($this->fileName, 'min.js')) {
            return;
        }

        $this->used = array();
        /* Iterate over lines to find lexicon strings
           in code file */
        $lines = $this->content;
        foreach ($lines as $line) {
            if ($this->type == 'Text') {
                if ((strpos($line, '[[%') === false) && (strpos($line, '[[!%') === false)) {
                    continue;
                }
            } elseif (strpos($line, $this->subPattern) === false) {
                continue;
            }

            $matches = array();
            preg_match($this->pattern, $line, $matches);
            if (isset($matches[2]) && !empty($matches[2])) {
                $this->addLexString($matches[2]);
            }
        }
    }

    /**
     * Add a lexicon string to $this->used array
     *
     * @param $string - lexicon string to add (with or without ~~)
     */
    public function addLexString($string) {
        $coreEntries = $this->modx->lexicon->getFileTopic($this->language);
        if (strstr($string, '~~')) {
            $this->squigglesFound++;
            $s = explode('~~', $string);
            $lexString = $s[0];
            $value = $s[1];
        } else {
            $lexString = $string;
            $value = '';
        }
        /* skip entries that are in the MODX lexicon and not re-defined */
        if (array_key_exists($lexString, $coreEntries) && empty($value)) {
            return;
        }
        /* Don't update an existing entry with an empty value */
        if (array_key_exists($lexString, $this->used) && (empty($value))) {
            return;
        }
        $this->used[$lexString] = $value;
    }
}

/**
 * Class PropertiesLexiconCodeFile
 *
 * Handles Lexicon strings in Properties transport files
 */
class PropertiesLexiconCodeFile extends LexiconCodeFile {

    /** Overrides parent method
     *  ($content is not used for these files)
     *
     *  @return array
     */
    public function _setContent($content = '') {
        return array();
    }


    /**
     * Overrides parent method
     */
    public function _setLexFiles(){
        $fullPath = $this->path . '/' . $this->fileName;
        if (file_exists($fullPath)) {
            $objects = include $fullPath;
            if (!is_array($objects)) {
                $msg = $this->modx->lexicon('mc_properties_not_an_array');
                $this->_setError($msg . ' ' .
                    $this->fileName);
            } else {
                foreach($objects as $object) {
                    if (isset($object['lexicon'])) {
                        if ($object['lexicon'] == $this->helpers->props['packageNameLower']) {
                            /* Correct if just the package name */
                            $object['lexicon'] = $object['lexicon'] . ':' . 'properties';
                        }
                        $this->addLexFile($object['lexicon']);
                        /* bail out at the first non-empty lexicon specification */
                        break;

                    }
                }
            }
            /* assume 'properties' topic if no topic specified */
            if (empty($this->lexFiles)) {
                $this->addLexFile('properties');
            }
        } else {
            $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
        }
        return;

    }

    /**
     * Overrides parent method
     */
    public function _setUsed(){
        $fullPath = $this->path . '/' . $this->fileName;
        if (file_exists($fullPath)) {
            $modx =& $this->modx;
            $objects = include $fullPath;
            if (! is_array($objects)) {
                $this->_setError('Not an array');
                return;
            }
            $_lang = $this->defined;
            /** @var $setting modSystemSetting */
            foreach ($objects as $object) {
                if (isset($object['desc']) && ( ! empty($object['desc']))) {
                    $this->addLexString($object['desc']);
                }
            }
        } else {
            $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
        }
    }
}

/**
 * Class SettingsLexiconCodeFile
 *
 * Handles Lexicon strings in Settings transport files
 */
class SettingsLexiconCodeFile extends LexiconCodeFile {

    /**
     * Overrides parent method
     * ($content is not used for these files)
     *
     * @return array
     */
    public function _setContent($content = '') {
        return array();
    }

    /**
     * Overrides parent method
     */
    public function _setLexFiles() {
        $this->addLexFile('default');
    }

    /**
     * Overrides parent method
     */
    public function _setUsed() {
        $fullPath = $this->path . '/' . $this->fileName;
        if (file_exists($fullPath)) {
            $modx =& $this->modx;
            $objects = include $fullPath;
            $_lang = $this->defined;
            /** @var $setting modSystemSetting */
            foreach ($objects as $setting) {
                $key = $setting->get('key');
                $name = $setting->get('name');
                if (empty($name)) {
                    $name = $key;
                }
                $description = $setting->get('description');
                if (empty($description)) {
                    $description = '';
                }
                $this->addLexString('setting_' . $key . '~~' . $name);
                $this->addLexString('setting_' . $key . '_desc' . '~~' . $description);
            }
        } else {
            $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
        }
    }
}

/**
 * Class MenuLexiconCodeFile
 *
 * Handles Lexicon strings in Settings transport files
 */
class MenuLexiconCodeFile extends LexiconCodeFile {

    /**
     * Overrides parent method
     * ($content is not used for these files)
     *
     * @return array
     */
    public function _setContent($content = '') {
        return array();
    }

    /**
     * Overrides parent method
     */
    public function _setLexFiles() {
        $fullPath = $this->path . '/' . $this->fileName;
        if (file_exists($fullPath)) {
            $modx = $this->modx;
            $objects = include $fullPath;
            if (!is_array($objects)) {
                $msg = $this->modx->lexicon('mc_properties_not_an_array');
                $this->_setError($msg .
                $this->fileName);
            } else {
                /** @var $object modMenu */
                foreach ($objects as $object) {
                    $action = $object->getOne('Action');
                    $topic = $action->get('lang_topics');
                    if ($topic !== null) {
                        if ($topic == $this->helpers->props['packageNameLower']) {
                            /* Correct if just the package name */
                            $topic = $topic . ':' . 'default';
                        }
                        $this->addLexFile($topic);
                        /* bail out at the first non-empty lexicon specification */
                        break;

                    }
                }
            }
            /* assume 'default' topic if no topic specified */
            if (empty($this->lexFiles)) {
                $this->addLexFile('default');
            }
        } else {
            $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
        }
        return;

    }

    /**
     * Overrides parent method
     */
    public function _setUsed() {
        $fullPath = $this->path . '/' . $this->fileName;
        if (file_exists($fullPath)) {
            $modx =& $this->modx;
            $objects = include $fullPath;
            if (!is_array($objects)) {
                $this->_setError('Not an array');
                return;
            }
            $_lang = $this->defined;
            /** @var $object modMenu */
            foreach ($objects as $object) {
                $string = $object->get('description');
                if (!empty($string)) {
                    $this->addLexString($string);
                }
            }
        } else {
            $this->_setError($this->modx->lexicon('mc_file_not_found' . ' ' . $fullPath));
        }
    }

}

