<?php
/**
 * helpers.class.php file for MyComponent Extra
 *
 * @author Bob Ray
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

/** Description:
 * -------------
 * Methods used by helpers class in MyComponent Extra
 */

class Helpers

{
    /* @var $modx modX - $modx object */
    public $modx;
    /* @var $props array - $scriptProperties array */
    public $props;
    /* @var $replaceFields array */
    protected $replaceFields;
    /* @var $tplPath string - path to MyComponent tpl directory */
    protected $tplPath;
    /* @var $source string - path to root of MyComponent */
    protected $source;
    /* @var $dirPermission - permission for new directories (from config file) */
    protected $dirPermission;
    /* @var $filePermission - permission for new files (from config file) */
    protected $filePermission;
    

    function  __construct(&$modx, &$props = array()) {
        $this->modx =& $modx;
        $this->props =& $props;
    }
    public function init() {
        $this->source = $this->props['source'];
        $this->tplPath = $this->source . '_build/utilities/' . $this->props['tplDir'];
        if (substr($this->tplPath, -1) != "/") {
            $this->tplPath .= "/";
        }
        $this->dirPermission = $this->props['dirPermission'];
        $this->filePermission = $this->props['filePermission'];

        $this->replaceFields = array(
            '[[+packageName]]' => $this->props['packageName'],
            '[[+packageNameLower]]' => $this->props['packageNameLower'],
            '[[+packageDescription]]' => $this->props['packageDescription'],
            '[[+author]]' => $this->props['author'],
            '[[+email]]' => $this->props['email'],
            '[[+copyright]]' => $this->props['copyright'],
            '[[+createdon]]' => $this->props['createdon'],
            '[[+authorSiteName]]' => $this->props['authorSiteName'],
            '[[+authorUrl]]' => $this->props['authorUrl'],
            '[[+packageUrl]]' => $this->props['packageUrl'],
            '[[+gitHubUsername]]' => $this->props['gitHubUsername'],
            '[[+gitHubRepository]]' => $this->props['gitHubRepository'],

        );
        $license = $this->getTpl('license');
        if (!empty($license)) {
            $license = $this->strReplaceAssoc($this->replaceFields, $license);
            $this->replaceFields['[[+license]]'] = $license;
        }
        unset($license);
    }
    public function getReplaceFields() {
        return $this->replaceFields;
    }
    public function replaceTags($text, $replaceFields = array()) {
        $replaceFields = empty ($replaceFields)? $this->replaceFields : $replaceFields;
        return $this->strReplaceAssoc($replaceFields, $text);
    }

    /**
     * Get tpl file contents from MyComponent build tpl directory
     *
     * @param $name string  - Name of tpl file
     * @return string - Content of tpl file or '' if it doesn't exist
     */
    public function getTpl($name)
    {
        if (strstr($name, '.php')) { /* already has extension */
            $text = @file_get_contents($this->tplPath . $name);
        } else { /* use .tpl extension */
            $text = @file_get_contents($this->tplPath . $name . '.tpl');
        }
        return $text !== false ? $text : '';
    }

    /**
     * Returns the correct filename for a given file
     *
     * @param $name - string name of object (mixed case OK)
     * @param $elementType string - 'modChunk', 'modResource', etc.
     * @param string $fileType string - Type of file to be created (code, properties, transport)
     * @return string
     *
     * Example returns for MyObject plugin-type object
     *    code:  myobject.plugin.php
     *    transport: transport.plugins.php
     *    properties: properties.myobject.plugin.php
     */
    public function getFileName($name, $elementType, $fileType = 'code') {
        /* $elementType is in the form 'modSnippet', 'modChunk', etc.
         * set default suffix to 'chunk', 'snippet', etc. */
        /* ToDo: Get suffix from config file */
        /* @var $elementObj modElement */
        $name = strtolower($name);
        $extension = 'php'; /* default extension */
        $suffix = substr(strtolower($elementType), 3); /* modPlugin -> plugin, etc.; default suffix */
        if ($suffix == 'templatevar') {
            $suffix = 'tv';
        }
        $output = '';

        if ($fileType == 'code') {
            switch ($elementType) {
                case 'modResource':
                    $suffix = 'content';
                case 'modTemplate':
                case 'modChunk':
                    $extension = 'html';
                case 'modSnippet':
                case 'modPlugin':
                    $output = $name .'.'. $suffix . '.' . $extension;
                    break;
                default:  /* all other elements get no code file */
                    $output = '';
                    break;

            }

        } elseif ($fileType == 'transport') {
            $output = 'transport.' . $suffix . 's.php';
        } elseif ($fileType = 'properties') {
            $output = 'properties.' . $name . '.' . $suffix . '.php';
        }
        /* replace any spaces with underscore */
        $output = str_replace(' ', '_', $output);
        return $output;
    }

    /**
     * @param $targetCore string - path to core directory in build
     * @param $type string - modSnippet, modChunk, etc.
     * @return string - full path for element code file (without filename or trailing slash)
     */
    public function getCodeDir ($targetCore, $type) {
        $dir = $targetCore . 'elements/';
        $type = $type == 'modTemplateVar' ? 'modTv' : $type;
        return $dir . strtolower(substr($type, 3)) . 's';
    }
    /**
     * @param $elementType string - 'modChunk', 'modSnippet', etc.
     * @return string - The name of the 'name' field for the object (name, pagetitle, etc.)
     */
    public function getNameAlias($elementType) {
        switch ($elementType) {
            case 'modTemplate':
                $nameAlias = 'templatename';
                break;
            case 'modCategory':
                $nameAlias = 'category';
                break;
            case 'modResource':
                $nameAlias = 'pagetitle';
                break;
            default:
                $nameAlias = 'name';
                break;
        }
        return $nameAlias;

    }


    /**
     * Write a file to disk - non-destructive -- will not overwrite existing files
     * Creates dir if necessary
     *
     * @param $dir string - directory for file (should not have trailing slash!)
     * @param $fileName string - file name
     * @param $content - file content
     * @param string $dryRun string - if true, writes to stdout instead of file.
     */
    public function writeFile ($dir, $fileName, $content, $dryRun = false) {

        if (!is_dir($dir)) {
            mkdir($dir, $this->dirPermission, true);
        }
        /* add trailing slash if not there */
        if (substr($dir, -1) != "/") {
            $dir .= "/";
        }
        /* write to stdout if dryRun is true */

        $file = $dryRun? 'php://output' : $dir . $fileName;
        if (empty($content)) {
            $this->modx->log(MODX::LOG_LEVEL_ERROR, '    No content for file ' . $fileName . ' (normal for chunks and templates until content is added)');
        }

        $fp = fopen($file, 'w');
        if ($fp) {
            if ( ! $dryRun) {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Creating ' . $file);
            }
            fwrite($fp, $content);
            fclose($fp);
            if (! $dryRun) {
                chmod($file, $this->filePermission);
            }
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Could not write file ' . $file);
        }


    }

    /**
     * Replaces all strings in $subject based on $replace associative array
     *
     * @param $replace array - associative array of key => value pairs
     * @param $subject string - text to do replacement in
     * @return string - altered text
     */
    public function strReplaceAssoc(array $replace, $subject)
    {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }

    /**
     * Recursive function copies an entire directory and its all descendants
     *
     * @param $source string - source directory
     * @param $destination string - target directory
     * @return bool - used only to control recursion
     */

    public function copyDir($source, $destination)
    {
        //echo "SOURCE: " . $source . "\nDESTINATION: " . $destination . "\n";
        if (is_dir($source)) {
            if (!is_dir($destination)) {
                mkdir($destination, $this->dirPermission, true);
            }
            $objects = scandir($source);
            if (sizeof($objects) > 0) {
                foreach ($objects as $file) {
                    if ($file == "." || $file == ".." || $file == '.git' || $file == '.svn') {
                        continue;
                    }

                    if (is_dir($source . '/' . $file)) {
                        $this->copyDir($source . '/' . $file, $destination . '/' . $file);
                    } else {
                        if ($file == 'build.config.php') continue;
                        if (strstr($file, 'config.php') && $file != $this->props['packageNameLower'] . '.config.php') continue ;
                        copy($source . '/' . $file, $destination . '/' . $file);
                    }
                }
            }
            return true;
        } elseif (is_file($source)) {
            return copy($source, $destination);
        } else {
            return false;
        }
    }
}
