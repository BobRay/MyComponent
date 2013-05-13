<?php
/**
 * lexiconhelper class file for MyComponent extra
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
    public $primaryLanguage;
    public $element; // current element being processed
    public $loadedLexiconFiles = array();
    public $lexiconCodeStrings = array();
    public $usedSomewhere = array();
    public $definedSomeWhere = array();
    public $lexiconFileStrings = array();
    public $missing = array();

    function  __construct(&$modx, &$props = array()) {
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

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_project')
            . ': ' . $this->helpers->getProp('packageName'));
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
            . ': ' .
            $this->modx->lexicon('mc_lexicon_helper')
            . "\n");
        $this->source = $this->helpers->getProp('mycomponentRoot');
        /* add trailing slash if missing */
        if (substr($this->source, -1) != "/") {
            $this->source .= "/";
        }


        $this->packageNameLower = $this->helpers->getProp('packageNameLower');
        $this->targetBase = $this->helpers->getProp('targetRoot');
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';
        $this->targetAssets = $this->targetBase . 'assets/components/' . $this->packageNameLower . '/';
        $this->primaryLanguage = $this->modx->getOption('primaryLanguage', $this->props, '');
        if (empty($this->primaryLanguage)) {
            $this->primaryLanguage = 'en';
        }
        clearstatcache(); /*  make sure is_dir() is current */
    }

    public function run() {
        $snippets = $this->modx->getOption('snippets',
            $this->helpers->getProp('elements', array()), array());
        $elements = array();
        /* get all plugins and snippets from config file */
        foreach ($snippets as $snippet => $fields) {
            if (isset($fields['name'])) {
                $snippet = $fields['name'];
            }
            $elements[trim($snippet)] = 'modSnippet';
        }
        $plugins = $this->modx->getOption('plugins',
            $this->helpers->getProp('elements', array()), array());
        foreach ($plugins as $plugin => $fields) {
            if (isset($fields['name'])) {
                $plugin = $fields['name'];
            }
            $elements[trim($plugin)] = 'modPlugin';
        }
        $chunks = $this->modx->getOption('chunks',
            $this->helpers->getProp('elements', array()), array());
        foreach ($chunks as $chunk => $fields) {
            if (isset($fields['name'])) {
                $plugin = $fields['name'];
            }
            $elements[trim($chunk)] = 'modChunk';
        }
        $templates = $this->modx->getOption('templates',
            $this->helpers->getProp('elements', array()), array());
        foreach ($templates as $template => $fields) {
            if (isset($fields['templatename'])) {
                $template = $fields['templatename'];
            }
            $elements[trim($template)] = 'modTemplate';
        }
        /* Add any resource content files to $elements array */
        $dir = $this->targetBase . '_build/data/resources';
        $resources = array();
        if (is_dir($dir)) {
            $this->helpers->resetFiles();
            $this->helpers->dirWalk($dir, 'html', true);
            $resources = $this->helpers->getFiles();
            foreach ($resources as $fileName => $directory) {
                $elements[$directory . '/' . $fileName] = 'modResource';
            }
        }

        /* Add any JS files to $elements array */
        $dir = $this->targetAssets . 'js';
        $jsFiles = array();
        if (is_dir($dir)) {
            $this->helpers->resetFiles();
            $this->helpers->dirWalk($dir, 'js', true);
            $jsFiles = $this->helpers->getFiles();
            foreach($jsFiles as $fileName => $directory) {
                $elements[$directory . '/' . $fileName] = 'jsFile';
            }
        }

        if (empty($elements)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_elements_to_process'));
            return;
        }
        $this->classFiles = array();
        $dir = $this->targetCore . 'model';
        /*  make sure there is a model dir */
        if (is_dir(($dir))) {
            $this->helpers->resetFiles();
            $this->helpers->dirWalk($dir, 'php', true);
            $this->classFiles = $this->helpers->getFiles();
            if (!empty($this->classFiles)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_found_these_class_files')
                 . ': ');
                foreach($this->classFiles as $name => $path) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "    " . $name);
                }
            }
        }

        foreach ($elements as $element => $type) {
            $this->element = $element;
            $this->included = array();
            $this->loadedLexiconFiles = array();
            $this->lexiconCodeStrings = array();
            $this->codeMatches = array();
            $this->missing = array();
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_stars'));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_processing_element')
                . ': ' . $element . " -- Type: " . $type);
            $this->getCode($element, $type);
            if (!empty($this->included)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_code_files_analyzed')
                     . ':'. "\n" . implode(", ", $this->included));
            }
            if (!empty($this->loadedLexiconFiles)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_lexicon_files_analyzed')
                    . ': ' . implode(', ', array_keys($this->loadedLexiconFiles)));
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_no_lexicon_files'));
            }
            $this->usedSomewhere = array_merge($this->usedSomewhere, $this->lexiconCodeStrings);

            $this->getLexiconFileStrings();
            $this->definedSomeWhere = array_merge($this->definedSomeWhere, $this->lexiconFileStrings);

            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,  count($this->lexiconCodeStrings) . ' ' .
                $this->modx->lexicon('mc_lexicon_strings_in_code_files'));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, count($this->lexiconFileStrings) . ' ' .
                $this->modx->lexicon('mc_lexicon_strings_in_lex_files'));

            $missing = $this->findMissing();
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->reportMissing($missing, $type));
        }
        $lexPropStrings = $this->getLexiconPropertyStrings();
        $this->checkPropertyDescriptions($lexPropStrings);

        /* remove ~~lexString from files is set in project config */

        $rewriteFiles = $this->modx->getOption('rewriteCodeFiles', $this->props, false);
        if ($rewriteFiles) {
            $this->rewriteFiles($snippets, $plugins, $templates, $chunks, $jsFiles, $resources);
        }
        $this->report();
    }

    public function rewriteFiles($snippets, $plugins, $templates, $chunks, $jsFiles, $resources) {
        if ( (!empty($snippets)) || (!empty($plugins)) || (!empty($templates)) || (!empty($chunks))
            || (!empty($jsFiles)) || (!empty($resources))) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n********" .
                $this->modx->lexicon('mc_removing')
                . ' ~~ ' .
                $this->modx->lexicon('mc_strings_from_code_files')
                 . '********');
        } else {
            return;
        }
        foreach ($snippets as $snippet => $fields) {
            $name = isset($fields['name']) ? $fields['name'] : $snippet;
            $name = strtolower($name);
            $fileName = isset($fields['filename']) ? $fields['filename'] : $name . '.' . 'snippet.php';
            $fileName = strtolower($fileName);
            $fullPath = $this->targetCore . 'elements/snippets/' . $fileName;
            $this->rewriteFile($fullPath, 'modScript');
            /*$propsFileName = $this->helpers->getFileName($name, 'modSnippet', 'properties');
            $propsFilePath = $this->targetBase . '_build/data/properties/' . $propsFileName;
            if (file_exists($propsFilePath)) {
                $this->rewriteFile($propsFilePath);
            }*/

        }
        $plugins = $this->modx->getOption('plugins',
            $this->helpers->getProp('elements', array()), array());
        foreach ($plugins as $plugin => $fields) {
            $name = isset($fields['name']) ? trim($fields['name']) : $plugin;
            $name = strtolower($name);
            $fileName = isset($fields['filename']) ? $fields['fileName'] : $name . '.' . 'plugin.php';
            $fileName = strtolower($fileName);
            $fullPath = $this->targetCore . 'elements/plugins/' . $fileName;
            $this->rewriteFile($fullPath, 'modScript');
            /*$propsFileName = $this->helpers->getFileName($name, 'modPlugin', 'properties');
            $propsFilePath = $this->targetBase . '_build/data/properties/' . $propsFileName;
            if (file_exists($propsFilePath)) {
                $this->rewriteFile($propsFilePath);
            }*/
        }

        foreach ($templates as $template => $fields) {
            $name = isset($fields['templatename'])
                ? $fields['templatename']
                : $template;
            $name = strtolower($name);
            $fileName = isset($fields['filename'])
                ? $fields['filename']
                : $name . '.' . 'template.html';
            $fileName = strtolower($fileName);
            $fullPath = $this->targetCore . 'elements/templates/' . $fileName;
            $this->rewriteFile($fullPath, 'modTemplate');
            /*$propsFileName = $this->helpers->getFileName($name, 'modtemplate', 'properties');
            $propsFilePath = $this->targetBase . '_build/data/properties/' . $propsFileName;
            if (file_exists($propsFilePath)) {
                $this->rewriteFile($propsFilePath);
            }*/

        }

        foreach ($chunks as $chunk => $fields) {
            $name = isset($fields['name'])
                ? $fields['name']
                : $chunk;
            $name = strtolower($name);
            $fileName = isset($fields['filename'])
                ? $fields['filename']
                : $name . '.' . 'chunk.html';
            $fileName = strtolower($fileName);
            $fullPath = $this->targetCore . 'elements/chunks/' . $fileName;
            $this->rewriteFile($fullPath, 'modChunk');
            /*$propsFileName = $this->helpers->getFileName($name, 'modChunk', 'properties');
            $propsFilePath = $this->targetBase . '_build/data/properties/' . $propsFileName;
            if (file_exists($propsFilePath)) {
                $this->rewriteFile($propsFilePath);
            }*/
        }



        foreach($this->classFiles as $name => $path) {
            $this->rewriteFile($path . '/' . $name, 'modScript');
        }
        foreach($jsFiles as $name => $path) {
            $this->rewriteFile($path . '/' . $name, 'modScript');
        }
        foreach($resources as $name => $path) {
            $this->rewriteFile($path . '/' . $name, 'modResource');
        }

        $propsFiles = array();
        $dir = $this->targetBase . '_build/data/properties';
        /*  make sure there is a model dir */
        if (is_dir(($dir))) {
            $this->helpers->resetFiles();
            $this->helpers->dirWalk($dir, 'php', true);
            $propsFiles = $this->helpers->getFiles();
            if (!empty($propsFiles)) {
                foreach ($propsFiles as $name => $path) {
                    $this->rewriteFile($path . '/' . $name, 'modScript');
                }
            }
        }

    }
    public function rewriteFile($path, $type = 'modScript') {
        if (file_exists($path)) {

            $content = file_get_contents($path);
            if (strstr($content, '~~')) {

                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_rewriting_code_file')
                    . ': ' . $path);
                if ($type == 'modScript') {
                    $pattern = '/~~.*([\'\"][\),])/';
                    $replace = '$1';
                } else {
                    $pattern = '/~~[^\]\?&]+/';
                    $replace = '';
                }

                $content = preg_replace($pattern, $replace, $content);

                if (!empty($content)) {
                    $fp = fopen($path, 'w');
                    if ($fp) {
                        fwrite($fp, $content);
                        fclose($fp);
                    }

                }
            }


        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' .
            $this->modx->lexicon('mc_no_code_file_at')
            . ': ' . $path);
        }
    }
    public  function getLexiconFileStrings() {
        $files = $this->loadedLexiconFiles;
        $included = array();
        foreach ($files as $fqn => $path) {
            if (!is_string($fqn) || $fqn == '') continue;
            $fileName = $path;
            if (!in_array($fqn, $included)) {
                $included[] = $fqn;
                if  (file_exists($fileName)) {
                    $_lang = null;
                    include $fileName;
                    if (is_array($_lang)) {
                        $this->lexiconFileStrings = array_merge($this->lexiconFileStrings, $_lang);
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_no_language_strings_in_file')
                        . ': ' . $fileName);
                    }
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_lexicon_file_nf')
                     . ': ' . $fileName);
                }
            }
        }
    }

    /**
     * Returns a fully qualified lexicon spec (e.g. 'example:en:default.inc.php')
     * @param $lexFileSpec (partial or full lexicon spec (e.g., default, en:default)
     * @return string
     */
    public function getLexFqn ($lexFileSpec) {
        $nspos = strpos($lexFileSpec, ':');
        $languages = array_keys($this->modx->getOption('languages', $this->props, array()));
        if (empty($languages)) {
            return '';
        }
        $language = $languages[0];
        $namespace = $this->helpers->getProp('packageNameLower');
        if ($nspos === false) {
            $topic_parsed = $lexFileSpec;

        } else { /* if namespace, search specified lexicon */
            $params = explode(':', $lexFileSpec);
            if (count($params) <= 2) {
                $namespace = $params[0];
                $topic_parsed = $params[1];
            }
            else {
                $language = $params[0];
                $namespace = $params[1];
                $topic_parsed = $params[2];
            }
        }
        return $language . ':' . $namespace . ':'  . $topic_parsed;
    }
    public function getLexiconFilePath($fqn) {
        $val = explode(':', $fqn);
        return $this->targetCore . 'lexicon' . '/' . $val[0] . '/' . $val[2] . '.inc.php';
    }

    public function findMissing() {
        $missing = array();
        $inCode = $this->lexiconCodeStrings;
        $inLexicon = $this->lexiconFileStrings;

        foreach($inCode as $key => $value) {

            if (! array_key_exists($key, $inLexicon)) {
                if (!array_key_exists($key, $missing)) {
                    $missing[$key] = $value;
                }
            }
        }
        if (is_array($inCode) && !empty($inCode) && empty($missing)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_missing_strings_in_lex_file'));
        }
        return $missing;

    }

    /**
     * Get appropriate quote character for a lexicon string
     * @param $value
     * @return string
     */
    public function getQc($value) {
        /* use double quote for strings containing a single quote */
        $qc = strchr($value, "'")
            ? '"'
            : "'";
        /* switch back if the single quote is escaped */
        $qc = strstr($value, "\'")
            ? "'"
            : $qc;
        return $qc;
    }

    public function reportMissing($missing, $type) {
        $output = "";
        if (!empty($missing)) {
            //$this->output .= "\nStrings missing from Language file(s):";
            $code = '';
            foreach ($missing as $key => $value) {
                $qc = $this->getQc($value);
                $code .= "\n\$_lang['" . $key . "'] = {$qc}" . $value . "{$qc};";
            }
            $count = count($this->loadedLexiconFiles);
            if (($count == 1) && isset($this->props['rewriteLexiconFiles'])
                    && $this->props['rewriteLexiconFiles']) {
                /* append $output to lexicon file  */
                $extra = $type == 'modPlugin' || $type == 'modSnippet'? ' or its included classes ' : '';
                $comment = '/* used in ' . $this->element . $extra .  ' */';
                $fileName = reset($this->loadedLexiconFiles);


                if (file_exists($fileName)) {
                    $content = file_get_contents($fileName);
                    $success = false;
                    if (strstr($content, $comment)) {
                        $content = str_replace($comment, $comment . $code, $content);
                        $fp = fopen($fileName , 'w');
                        if ($fp) {
                            fwrite($fp, $content);
                            fclose($fp);
                            $success = true;
                        }
                    } else {
                        $fp = fopen($fileName, 'a');
                        if ($fp) {
                            fwrite($fp, "\n\n" . $comment . $code);
                            fclose($fp);
                            $success = true;
                        } else {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                                $this->modx->lexicon('mc_could_not_open_lex_file')
                             . ': ' . $fileName );

                        }
                    }
                    if ($success) {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_updated_lex_file')
                                . ' -- ' . key($this->loadedLexiconFiles) . ' ' .
                                $this->modx->lexicon('mc_with_these_strings')
                                . ":\n" . $code);
                    }
                }


            } elseif (isset($this->props['rewriteLexiconFiles'])
                    && $this->props['rewriteLexiconFiles']) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_cannot_update_multiple_lex_files')
                . ";\n" .
                $this->modx->lexicon('mc_paste_these_strings')
                . ":\n" . $code);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_missing_lex_strings')
                     . ': ' . $code);

            }
        }

        return $output;

    }

    public function findUnused() {
        $unused = array();
        if (!empty($this->usedSomewhere) && !empty($this->definedSomeWhere)) {
            foreach ($this->definedSomeWhere as $key => $value) {
                if( !array_key_exists($key, $this->usedSomewhere))
                    $unused[$key] = $value;
            }

        }
        return $unused;
    }
    public function reportUnused($unused) {
        $output = '';
        if (!empty($unused)) {
            $code = '';
            foreach($unused as $key => $value) {
                /* skip System Setting strings */
                if (! strstr($key, 'setting_')) {
                    $code .= "\n    \$_lang['" . $key . "'] = '" . $value . "';";
                }
            }
            if (!empty($code)) {
                $output =  $this->modx->lexicon('mc_lex_strings_never_used')
                 . ":\n" . $code;
            } else {
                $output =  $this->modx->lexicon('mc_no_unused_lex_strings');
            }
        } else {
            $output =  $this->modx->lexicon('mc_no_unused_lex_strings');
        }
        return $output;

    }
    public function findUndefined() {
        $undefined = array();
        foreach ($this->usedSomewhere as $key => $value) {
            if (!array_key_exists($key, $this->definedSomeWhere)) {
                $undefined[$key] = $value;
            }
        }
        return $undefined;

    }

    public function reportUndefined($undefined) {
        $output = '';
        if (!empty($undefined)) {

            $output =  count($undefined) . ' ' .
                $this->modx->lexicon('mc_missing_lex_strings');
        } else {
            $output =  $this->modx->lexicon('mc_all_lex_strings_defined');
        }
        return $output;
    }

    public function findEmpty() {
        $empty = array();
        foreach ($this->definedSomeWhere as $key => $value) {
            if (empty($value)) {
                $empty[] = $key;
            }
        }
        return $empty;
    }

    public function reportEmpty($empty) {
        $output = '';
        if (empty($empty)) {
            $output  =  $this->modx->lexicon('mc_no_empty_lex_strings_in_files');
        } else {
            $output =  $this->modx->lexicon('mc_empty_lex_strings');
            foreach ($empty as $string) {
                $output .=  "\n    \$_lang['" . $string . "'] = '';";
            }
        }
        return $output;
    }

    public function report() {
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->checkSystemSettingDescriptions());
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n******** " .
            $this->modx->lexicon('mc_final_audit')
                 . '********');
        $undefined = $this->findUndefined();
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->reportUndefined($undefined));

        $unused = $this->findUnused();
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->reportUnused($unused));
        $empty = $this->findEmpty();
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->reportEmpty($empty));
        //echo $this->output;
    }

    public function getLexiconPropertyStrings() {
        $_lang = array();
        $lexiconFilePath = $this->targetCore . 'lexicon/' . $this->primaryLanguage . '/' . 'properties.inc.php';
        if (file_exists($lexiconFilePath)) {
            require $lexiconFilePath;
        }
        return $_lang;
    }

    /**
     * Check  lexicon properties.inc.php for property descriptions,
     * output strings.
     */
    public function checkPropertyDescriptions($lexStrings) {

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n******** " .
            $this->modx->lexicon('mc_checking_for_property_lex_strings')
                 . '********');
        $ary = $this->modx->getOption('elements', $this->props, array());
        foreach($ary as $type => $elementList) {

            $elements = empty($elementList)? array() : $elementList;
            foreach ($elements as $element => $fields ) {
                $realType = 'mod' . ucfirst(substr($type, 0, -1));
                $propsFileName = $this->helpers->getFileName($element, $realType, 'properties');
                $propsFilePath = $this->targetBase . '_build/data/properties/' . $propsFileName;
                /* process one properties file */
                $missing = array();
                $empty = array();
                if (file_exists($propsFilePath)) {
                    $props = include $propsFilePath;
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n********\n" .
                        $this->modx->lexicon('mc_checking_properties_for')
                    . ' ' . $element . ' -- Type: ' . $type);
                    if (!is_array($props)) {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_no_properties_in')
                                . ' ' . $propsFileName);
                    } else {
                        foreach($props as $prop) {
                            $description = $prop['desc'];

                            if (strstr($description, '~~')) {
                                $s = explode('~~', $description);
                                $lexKey = $s[0];
                            } else {
                                $lexKey = $description;
                            }
                            if ( ! array_key_exists($lexKey, $lexStrings)) {
                                $missing[] = $description;
                            } else {
                                if (isset($s[1])) {
                                    if ($lexStrings[$lexKey] != $s[1] ) {
                                        $empty[$lexKey] = $s[1];
                                    }
                                }

                            }
                        }
                        $comment = "/* Used in " . $propsFileName . " */";
                        $this->updateLexiconPropertiesFile($missing, $empty, $comment);
                    }

                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' .
                        $this->modx->lexicon('mc_no_properties_file_for')
                        . ' ' . $element . ' ' . $propsFilePath);
                }
            }
        }
    }

    public function updateLexiconPropertiesFile($missing, $empty, $comment) {
        $emptyFixed = 0;
        $code = '';
        if (empty($missing) && empty($empty) ) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_missing_property_descriptions_in_lex_file'));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_empty_property_descriptions_in_lex_file'));
            return;
        } else {
            $lexFile = $this->targetCore . '/lexicon/' . $this->primaryLanguage . '/properties.inc.php';
            $lexFileContent = file_get_contents($lexFile);
            $original = $lexFileContent;
        }

        if (empty($missing)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_missing_property_description_lex_strings'));
        } else {
            foreach ($missing as $string) {
                $val = strstr($string, '~~') ? explode('~~', $string) : array($string,'');
                $qc = $this->getQc($val[1]);
                $code  .= "\n\$_lang['" . $val[0] . "'] = {$qc}" . $val[1] . "{$qc};";
            }
            if (strstr($lexFileContent, $comment)) {
                $lexFileContent = str_replace($comment, $comment . $code,$lexFileContent);
            } else {
                $lexFileContent .= "\n\n" . $comment . $code . "\n";
            }
        }
        if (!empty ($empty)) {
            foreach ($empty as $key => $value) {
                $pattern = "/(_lang\[')" . $key . "(']\s*=\s* )'.*'/";
                $qc = $this->getQc($value);
                $replace = "$1$key$2{$qc}" . $value . "{$qc}";
                preg_match($pattern, $lexFileContent, $matches);
                $count = 0;
                $lexFileContent = preg_replace($pattern, $replace, $lexFileContent,  1, $count);
                $emptyFixed += $count;
            }
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_updated_lex_strings_with_these_keys'));
                foreach($empty as $key => $value) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n    " . $key);
                }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_no_empty_property_descriptions_in_lex_file'));
        }
        if (isset($this->props['rewriteLexiconFiles'])
                && $this->props['rewriteLexiconFiles']
                && (!empty($missing) || $emptyFixed)) {
            /* make sure we're not shortening it */
            if (strlen($lexFileContent) > strlen($original)) {
                $fp = fopen($lexFile, 'w');
                /* make sure we can open file */
                if ($fp) {
                    fwrite($fp, $lexFileContent);
                    fclose($fp);
                    if (!empty($missing)) {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_updated')
                            . ' ' . 'properties.inc.php' . ' '  .
                                $this->modx->lexicon('mc_entries_with_these_keys')
                            . ': ');
                        foreach($missing as $key => $value) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "    " . $value);
                        }
                        if ($emptyFixed) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "Fixed " . $emptyFixed . ' empty lexicon string(s)');
                        }
                    }

                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_could_not_open_lex_properties_file')
                        . ': ' . $lexFile);
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                    $this->modx->lexicon('mc_failed_to_update_lex_file')
                        . ' ' . $lexFile);
            }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_lex_properties_code_to_add'));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" . $comment . "\n" . $code . "\n\n");
        }
    }

    /* ToDo: checkSystemEventDescriptions() ?? */

    public function checkSystemEventDescriptions(){
        /* don't know where the hell these are (if anywhere)
           There's no hover help for them in the Manager */
    }


    public function checkSystemSettingDescriptions() {
        /*
        * These should be in the default topic  (checked).
        * Check for both name and description  lex strings (not key):
        * setting_mySetting   Name of mySetting
        * setting_mySetting_desc   Description of mySetting
         *
        * Note: In the Manager, just update the system setting
        * and add the name and description (don't use keys or underscores)
        */
        /* ToDo: Update lexicon file */

        $comment = "/* System Setting Names and Descriptions */";
        $settings = $this->modx->getOption('newSystemSettings', $this->props, array());
        $output = '';
        if (!empty($settings)) {
            $_lang = array();
            $missing = array();
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n********  " .
                $this->modx->lexicon('mc_checking_for_ss_lex_strings')
                . '********');
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon("mc_checking_ss_names_descriptions"));
            $fqn = $this->getLexFqn('default');
            $fileName = $this->getLexiconFilePath($fqn);
            include $fileName;

            if (empty($_lang)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_no_lex_strings_in_dip'));
                // return '';
                $_lang = array();
            }
            foreach($settings as $key => $value ) {
                $key = strtoLower($key);
                $lexNameKey = 'setting_' . $key;
                $lexDescKey = 'setting_' . $key . '_desc';
                if ( !in_array($lexNameKey, array_keys($_lang))) {
                 $missing[$lexNameKey] = '';
                }
                if (!in_array($lexDescKey, array_keys($_lang))) {
                 $missing[$lexDescKey] = '';
                }
            }
        if (!empty($missing)) {

            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_missing_from_dip')
            . "\n");
            $this->modx->lexicon->load($this->primaryLanguage . ':' . $this->packageNameLower . ':default');
            $code = '';
            foreach ($missing as $key => $value) {
             /* use values from MODX Lexicon Management, if set */
                $dbValue = $this->modx->lexicon($key);
                $value = $dbValue != $key? $dbValue : $value;
                $qc = strstr($value, "'") ? '"' : "'";
                $code .= "\n\$_lang['" . $key . "'] = {$qc}" . $value . "{$qc};";
            }
            if ($this->helpers->getProp('rewriteLexiconFiles', false)) {
                $content = file_get_contents($fileName);
                $success = false;
                if (! strstr($content, $comment)) {
                    $fp = fopen($fileName, 'a');
                    if ($fp) {
                        fwrite ($fp, "\n\n" . $comment . $code);
                        fclose($fp);
                        $success = true;
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                            $this->modx->lexicon('mc_could_not_open_dip_append'));
                    }
                } else {
                    $content = str_replace($comment, $comment . $code, $content);
                    $fp = fopen($fileName, 'w');
                    if ($fp) {
                        fwrite($fp, $content);
                        fclose($fp);
                        $success = true;
                    }
                    else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                            $this->modx->lexicon('mc_could_not_open_dip_write'));
                    }
                }
                if ($success) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                        $this->modx->lexicon('mc_update_these_strings_in dip')
                        . ":\n" . $code);
                }

            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_strings_missing_from_dip')
                      . ': '  . $code);
            }
        }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_no_ss_names'));
        }
    return $output;
    }


    public function addClassFiles($dir, $file) {
        //$this->output .= "\nIn addClassFiles";
        $this->classFiles[$file] = $dir;
    }

    /**
     * returns raw code from an element file and all
     * the class files it includes
     *
     * @param $element string - name of element
     * @param $type string - 'modSnippet or modChunk
     */
    public function getCode($element, $type) {
        $file = '';
        if (empty($element)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                $this->modx->lexicon('mc_lex_element_is_empty'));
            return;
        }
        /* $element is the full path for js files */
        if ($type == 'jsFile') {
            $file = $element;
        } elseif($type == 'modResource') {
            $file = $element;

        } else {
            /* get name of element directory */
            $dirName = strtolower(substr($type, 3)) . 's';

            /* Check for explicit filename */
            $elementFileName = $this->modx->getOption('filename',
                $this->props['elements'][$dirName][$element], '' );

            if (!empty ($elementFileName)) {
                $file = $this->targetCore . 'elements/' . $dirName . '/' . $elementFileName;
            } else {
                if ($type == 'modSnippet' || $type == 'modPlugin') {
                    $suffix = '.php';
                } else {
                    $suffix = '.html';
                }
                $file = $this->targetCore . 'elements/' . $dirName . '/' .
                    strtolower($element) . '.' . substr($dirName, 0, -1) . $suffix;
            }

        }
        if (file_exists($file)) {
            $this->included[] = $file;
            $this->getIncludes($file, $type);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, ' ' .
                $this->modx->lexicon('mc_file_nf')
                . ': ' . $file);
        }

    }

    /**
     * Searches for included .php files in code
     * and appends their content to $code reference var
     *
     * Also populates $this->lexiconCodeStrings and $this->loadedLexiconFiles
     *
     * @param $file - path to code file(s)
     * @param $type - modSnippet, modChunk, jsFile, etc.
     */
    public function getIncludes($file, $type) {
        $matches = array();
        $lines = array();
        // $fp = fopen($file, "r");
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (!empty($content)) {
                // $content = $this->helpers->strip_comments($content);
                $lines = explode ("\n", $content);
                unset($content);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_file_is_empty')
                        . ' '. $file);
            }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                $this->modx->lexicon('mc_file_nf')
                    . ' ' . $file);
            return;
        }

        $fileName = 'x';

        foreach ($lines as $line) {
            /* process lexicon->load() lines */

            if (strstr($line,'lexicon->load')) {
                $matches = array();
                preg_match('#lexicon->load\s*\s*\(\s*\'(.*)\'#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    /* skip dynamic lex loads */
                    if (strpos($matches[1], '$')) {
                        continue;
                    }
                    $fqn = $this->getLexFqn($matches[1]);
                    if (! in_array($fqn, array_keys($this->loadedLexiconFiles ))) {
                        $this->loadedLexiconFiles[$fqn] = $this->getLexiconFilePath($fqn);
                    }
                }

            /* process lexicon entries */
            } elseif (strstr($line, 'modx->lexicon')) {

                /* ignore lines with # comments */
                if (strstr($line, '#')) {
                    continue;
                }
                $matches = array();
                preg_match('#modx->lexicon\s*\s*\(\s*[\'\"]([^\)]*)[\'\"]#', $line, $matches);
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
                    } elseif (empty($this->lexiconCodeStrings[$lexString]) && !empty($value)) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    }
                }
            } elseif ($type == 'jsFile') {
                $matches = array();
                preg_match('#_\(\s*[\'\"](.*)[\'\"]\)#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (strstr($matches[1], '~~')) {
                        $s = explode('~~', $matches[1]);
                        $lexString = $s[0];
                        $value = $s[1];
                    } else {
                        $lexString = $matches[1];
                        $value = '';
                    }
                    if (!in_array($lexString, array_keys($this->lexiconCodeStrings))) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    } elseif (empty($this->lexiconCodeStrings[$lexString]) && !empty($value)) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    }
                }
            /* handle lexicon tags */
            } elseif (strstr($line, '[' . '[' . '%') || strstr($line, '[' . '[' . '!' . '%')) {
                $matches = array();
                preg_match('#&topic=`(.*)`#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    $topic = $matches[1];
                } else {
                    $topic = 'default';
                }
                $fqn = $this->getLexFqn($topic);
                if (!in_array($fqn, array_keys($this->loadedLexiconFiles))) {
                    $this->loadedLexiconFiles[$fqn] = $this->getLexiconFilePath($fqn);
                }
                $matches = array();
                preg_match('#\[\[!*%([^\?&\]]*)#', $line, $matches);
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (strstr($matches[1], '~~')) {
                        $s = explode('~~', $matches[1]);
                        $lexString = $s[0];
                        $value = $s[1];
                    } else {
                        $lexString = $matches[1];
                        $value = '';
                    }
                    if (!in_array($lexString, array_keys($this->lexiconCodeStrings))) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    } elseif (empty($this->lexiconCodeStrings[$lexString]) && !empty($value)) {
                        $this->lexiconCodeStrings[$lexString] = $value;
                    }
                }

            }
            /* recursively process includes files */
            if (strstr($line, 'include') || strstr($line, 'include_once') || strstr($line, 'require') || strstr($line, 'require_once')) {

                preg_match('#[0-9a-zA-Z_\-\s]*\.class\.php#', $line, $matches);
                $fileName = isset($matches[0]) && !empty($matches[0]) ? $matches[0] : 'x';

            }

            /* check files included with getService() and loadClass() */
            if (strstr($line, 'modx->getService')) {
                $matches = array();
                $pattern = "/modx\s*->\s*getService\s*\(\s*\'[^,]*,\s*'([^']*)/";
                preg_match($pattern, $line, $matches);
                if (!isset($matches[1])) continue;
                $s = strtoLower($matches[1]);
                if (strstr($s, '.')) {
                    $r = strrev($s);
                    $fileName = strrev(substr($r, 0, strpos($r, '.')));
                }
                else {
                    $fileName = $s;
                }
            }
            if (strstr($line, 'modx->loadClass')) {
                $pattern = "/modx\s*->\s*loadClass\s*\(\s*\'([^']*)/";
                preg_match($pattern, $line, $matches);
                if (!isset($matches[1])) continue;

                $s = strtoLower($matches[1]);
                if (strstr($s, '.')) {
                    $r = strrev($s);
                    $fileName = strrev(substr($r, 0, strpos($r, '.')));
                }
                else {
                    $fileName = $s;
                }
            }

            $fileName = strstr($fileName, 'class.php')
                ? $fileName
                : $fileName . '.class.php';
            if (isset($this->classFiles[$fileName])) {

                // skip files we've already included
                if (!in_array($fileName, $this->included)) {
                    $this->included[] = $fileName;
                    $this->getIncludes($this->classFiles[$fileName] . '/' . $fileName, $type);
                }
            }
        }
    }
}
