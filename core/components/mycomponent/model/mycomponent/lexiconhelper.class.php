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

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_project')
            . ': ' . $this->helpers->getProp('packageName'));
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
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
            'processors' => array (
                $this->targetCore . 'processors/' => 'processors',
            ),
            'build files' => array (
                $this->targetBase . '_build/' => 'build files',
            ),
            'assets Files' => array(
                $this->targetAssets => 'assets Files',

            ),
        );
        foreach($toProcess as $processing => $directories) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_stars'));
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
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
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                            "\n    " . $this->modx->lexicon('mc_processing')  .
                            ' ' . $msg);
                    }
                    $this->processFiles($files);
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                        "        " . $this->modx->lexicon('mc_no_code_files'));
                }
            }
        }
        $this->finalAudit();

        $src = rtrim($this->targetLexDir, '/\\');
        $dst = MODX_CORE_PATH . 'components/' . $this->packageNameLower . '/lexicon';
        $this->helpers->copyDir($src, $dst);

        return;
    }


    public function processFiles($files) {
        foreach($files as $fileName => $fullPath) {
            if (strstr($fileName, 'min.js') || strstr($fileName, 'build.transport.php')) {
                continue;
            }
            $this->processFile($fileName, $fullPath );
        }
    }

    public function processFile($fileName, $fullPath) {
        $indent = '        ';
        $rewriteLexiconFiles = $this->helpers->getProp('rewriteLexiconFiles', false);
        $rewriteCodeFiles = $this->helpers->getProp('rewriteCodeFiles', false);

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . $indent .
            $this->modx->lexicon('mc_processing') .
            ' ' . $fileName);

        /* get appropriate LexiconCodeFile object from Factory */
        $lcf = LexiconCodeFileFactory::getInstance($this->modx, $this->helpers,
            $fullPath, $fileName, $this->targetLexDir);
        $lexFiles = $lcf->lexFiles;
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $indent .
            $this->modx->lexicon('mc_lex_files') .
            ': ' . implode (', ', array_keys($lexFiles)));
        /* Create lexicon files if necessary */
        foreach ($lexFiles as $file => $path) {
            if ((!empty($path)) && (!file_exists($path))) {
                $dir = str_replace($file, '', $path);
                $this->createLexiconFile($dir, $file);
            }
        }
        $used = $lcf->used;
        $defined = $lcf->defined;
        if (!empty($used)) {
            $this->usedSomewhere = array_merge($this->usedSomewhere, $used);
            $this->definedSomewhere = array_merge($this->definedSomewhere, $defined);

            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                count($used) . ' ' .
                $this->modx->lexicon('mc_lex_strings_in_code_file'));

            $missing = $lcf->missing;
            if (!empty($missing)) {

                if (count($lexFiles) > 1) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                        $this->modx->lexicon('mc_cannot_update_multiple_lex_files')
                        . ";\n" . $this->modx->lexicon('mc_paste_these_strings')
                              . ':'
                        );
                } elseif ($rewriteLexiconFiles) {
                    $lcf->updateLexiconFile();
                    reset($lexFiles);
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                        $this->modx->lexicon('mc_updated_lex_file')
                        . ' ' . key($lexFiles) . ' ' .
                        $this->modx->lexicon('mc_with_these_strings')
                        . ':'
                        );
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                        $this->modx->lexicon('mc_missing_lex_strings')
                              . ':'
                        );
                }
                $code = $this->_formatLexStrings($missing);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_stars'));
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, $code, true);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_stars'));

            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                    $this->modx->lexicon('mc_all_lex_strings_defined'));
            }

            if ($rewriteCodeFiles) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                    $this->modx->lexicon('mc_rewriting_code_file'));
                $lcf->updateCodeFile();
            }


        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '            ' .
                $this->modx->lexicon('mc_no_language_strings_in_file'));
        }


    }
    public function _formatLexStrings($strings) {
        $code = '';
        foreach ($strings as $key => $value) {
            $code .= "\n\$_lang['" . $key . "'] = " . var_export($value, true) . ";";
        }
        return $code;
    }

    public function finalAudit() {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_stars'));
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_stars'));
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_final_audit'));
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

        // echo 'DEFINED: ' . print_r($defined, true);
        // echo 'USED: ' . print_r($used, true);

        /* report undefined entries */
        if (empty($undefined)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_all_lex_strings_defined'));
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_final_audit_undefined'));
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->_formatLexStrings($undefined));
        }
        /* report undefined entries */
        if (empty($unused)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_no_unused_lex_strings'));
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_lex_strings_never_used'));
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->_formatLexStrings($unused, true));
        }
        /* report empty entries */
        if (empty($emptyEntries)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_no_empty_lex_strings_in_files'));
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_empty_lex_strings'));
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->_formatLexStrings($emptyEntries, true));
        }
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

}
