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
    public $targetBase;
    public $targetCore;
    public $targetAssets;
    public $targetData;
    public $targetLexDir;
    public $rewriteLexiconFiles;
    public $rewriteCodeFiles;
    public $dirPermission;
    public $filePermission;
    public $primaryLanguage;
    public $usedSomewhere = array();
    public $definedSomewhere = array();


    function  __construct(&$modx, &$props = array()) {
        $this->modx =& $modx;
        $this->props =& $props;
    }

    /**
     * Initialize class
     *
     * @param array $scriptProperties - (optional) $scriptProperties array
     * @param string $currentProject - (optional) project name (used for unit testing)
     */
    public function init($scriptProperties = array(), $currentProject = '') {
        clearstatcache(); /*  make sure is_dir() is current */

        require_once dirname(__FILE__) . '/mcautoload.php';
        spl_autoload_register('mc_auto_load');

        require_once dirname(__FILE__) . '/lexiconcodefile.class.php';



        // Get the project config file
        if ($currentProject == '') {
            $currentProjectPath = $this->modx->getOption('mc.root', null,
                $this->modx->getOption('core_path') . 'components/mycomponent/') . '_build/config/current.project.php';
            if (file_exists($currentProjectPath)) {
                include $currentProjectPath;
            } else {
                session_write_close();
                die('Could not find current.project.php file at: ' . $currentProjectPath);
            }

        }
        if (empty($currentProject)) {
            session_write_close();
            die('No current Project Set');
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

        /* Make sure that we have usable values */
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

        $this->packageNameLower = $this->helpers->getProp('packageNameLower');
        $this->targetBase = $this->helpers->getProp('targetRoot');
        $this->targetBase = str_replace('\\', '/', $this->targetBase);
        $this->targetBase = strtolower($this->targetBase);
        $this->targetData = $this->targetBase . '_build/data/';
        $this->targetData = str_replace('\\', '/', $this->targetData);
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';
        $this->targetAssets = $this->targetBase . 'assets/components/' . $this->packageNameLower . '/';
        $this->primaryLanguage = $this->modx->getOption('primaryLanguage', $this->props, '');
        $this->targetLexDir = $this->targetCore . 'lexicon/';
        $this->rewriteLexiconFiles = $this->helpers->getProp('rewriteLexiconFiles', false);
        $this->rewriteCodeFiles = $this->helpers->getProp('rewriteCodeFiles', false);

        if (empty($this->primaryLanguage)) {
            $this->primaryLanguage = 'en';
        }
        $this->targetLexDir = $this->targetCore . 'lexicon/';
        clearstatcache(); /*  make sure is_dir() is current */
    }

    public function run() {
        $x = 1;
        $toProcess = array(
            'elements' => array (
                $this->targetCore . 'elements/chunks/' => 'chunks',
                $this->targetCore . 'elements/snippets/' => 'snippets',
                $this->targetCore . 'elements/plugins/' => 'plugins',
                $this->targetCore . 'elements/templates/' => 'templates',
            ),
            'classes' => array (
                $this->targetCore . 'model/' => 'classes',
            ),
            'build files' => array (
                $this->targetBase . '_build/data/' => 'build files',
            ),
            'assets Files' => array(
                $this->targetAssets => 'assets Files',

            ),
        );
        foreach($toProcess as $processing => $directories) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_stars'));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_processing') .
                ' ' . $processing);
            foreach( $directories as $dir => $msg) {
                if (! is_dir($dir)) {
                    continue;
                }
                $this->helpers->resetFiles();
                $types = '.php,html,js';
                $this->helpers->dirWalk($dir, $types, true);
                $files = $this->helpers->getFiles();
                if (!empty($files)) {
                    if (count($directories) > 1) {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            '    ' . $this->modx->lexicon('mc_processing')  .
                            ' ' . $msg);
                    }

                    $this->processFiles($files);
                }
            }
        }
        $this->finalAudit();
        return;
    }


    public function processFiles($files) {
        foreach($files as $fileName => $fullPath) {
            if (strstr($fileName, 'min.js')) {
                continue;
            }
            /* This is so it can run on MyComponent itself */
            if (strstr($fileName, 'lexicon')) {
                continue;
            }
            $this->processFile($fileName, $fullPath );
        }
    }

    public function processFile($fileName, $fullPath) {
        $indent = '        ';
        $rewriteLexiconFiles = $this->helpers->getProp('rewriteLexiconFiles', false);
        $rewriteCodeFiles = $this->helpers->getProp('rewriteCodeFiles', false);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $indent .
            $this->modx->lexicon('mc_processing') .
            ' ' . $fileName);
        $lcf = LexiconCodeFileFactory::getInstance($this->modx, $this->helpers, $fullPath, $fileName, $this->targetLexDir);
        $lexFiles = $lcf->lexFiles;

        foreach ($lexFiles as $file => $path) {
            if ((!empty($path)) && (!file_exists($path))) {
                $dir = str_replace($file, '', $path);
                $this->createLexiconFile($dir, $file);
            }
            $used = $lcf->used;
            $defined = $lcf->defined;
            if (!empty($used)) {
                $this->usedSomewhere = array_merge($this->usedSomewhere, $used);
                $this->definedSomewhere = array_merge($this->definedSomewhere, $defined);


                if ($rewriteLexiconFiles) {
                    $lcf->updateLexiconFile();
                }
                if ($rewriteCodeFiles) {
                    $lcf->updateCodeFile();
                }
            }
        }

    }

    public function finalAudit() {
        $defined = $this->definedSomewhere;
        $used = $this->usedSomewhere;
        $undefined = array();
        $unused = array();
        $lexDir = $this->targetCore . 'lexicon/' . $this->primaryLanguage;
        $this->helpers->resetFiles();
        $this->helpers->dirWalk($lexDir, '.php', false);
        $lexFiles = $this->helpers->getFiles();
        $_lang = array();
        foreach ($lexFiles as $fileName => $dir) {
            include($dir . '/' . $fileName);
        }
        $defined = $_lang;
        $undefined = array();
        $unused = array();
        foreach ($defined as $key => $value) {
            if (!array_key_exists($key, $used)) {
                $unused[$key] = $value;
            }
        }
        foreach ($used as $key => $value) {
            if (!array_key_exists($key, $defined)) {
                $undefined[$key] = $value;
            }
        }
        $filtered = array_filter($defined);
        $emptyEntries = array_diff($defined, $filtered);
        echo 'DEFINED: ' . print_r($defined, true);
        echo 'USED: ' . print_r($used, true);
        echo 'EMPTY: ' . print_r($emptyEntries, true);
        echo 'UNDEFINED: ' . print_r($undefined, true);
        echo 'UNUSED: ' . print_r($unused, true);
    }

    public function createLexiconFile($dir, $fileName) {
        $language = $this->primaryLanguage;
        $tpl = $this->helpers->getTpl('phpfile.php');
        $tpl = str_replace('[[+elementName]]', $language . ':' . $fileName . ' topic', $tpl);
        $tpl = str_replace('[[+description]]', $language . ':' . $fileName . ' topic lexicon strings', $tpl);
        $tpl = str_replace('[[+elementType]]', 'lexicon file', $tpl);
        $tpl = $this->helpers->replaceTags($tpl);
        $this->helpers->writeFile($dir, $fileName, $tpl);
    }


    public function getLexiconFilePath($fqn) {
        $val = explode(':', $fqn);
        return $this->targetCore . 'lexicon' . '/' . $val[0] . '/' . $val[2] . '.inc.php';
    }



    public function reportMissing($missing, $type) {
        $code = '';
                    if ('xx') {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                            $this->modx->lexicon('mc_updated_lex_file')
                                . ' -- ' . key($this->loadedLexiconFiles) . ' ' .
                                $this->modx->lexicon('mc_with_these_strings')
                                . ":\n" . $code);
                    }


        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_cannot_update_multiple_lex_files')
                . ";\n" .
                $this->modx->lexicon('mc_paste_these_strings')
                . ":\n" . $code);

                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_missing_lex_strings')
                     . ': ' . $code);
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


    /**
     * Check  lexicon properties.inc.php for property descriptions,
     * output strings.
     */

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

    /* ToDo: Move to LexiconCodeFile class */
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


}
