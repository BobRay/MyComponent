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
        $name = strtolower($name);
        if (strstr($name, '.php')) { /* already has extension */
            $text = @file_get_contents($this->tplPath . 'my' . $name);
            if (empty($text)) {
                $text = @file_get_contents($this->tplPath . $name);
            }
        } else { /* use .tpl extension */
            $text = @file_get_contents($this->tplPath . 'my' .  $name . '.tpl');
            if (empty($text)) {
                $text = @file_get_contents($this->tplPath . $name . '.tpl');
            }
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

    /**
     * @param $values array - array from project config file
     * @param $intersectType string (modTemplateVarTemplate, modPluginEvent, etc.)
     * @param $mainObjectType string - (modTemplate, modSnppet, etc.)
     * @param $subsidiaryObjectType string - (modTemplate, modSnippet, etc.)
     * @param $fieldName1 string - intersect field name for main object.
     * @param $fieldName2 string - intersect field name for subsidiary object.
     */
    public function createIntersects($values, $intersectType, $mainObjectType, $subsidiaryObjectType, $fieldName1, $fieldName2 ) {
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating ' . $intersectType . ' objects');


        if ($intersectType == 'modPluginEvent') {
            /* create new System Event Names record, if set in config */
            /* @var $obj modEvent */
            $newEvents = $this->props['newSystemEvents'];
            $newEventNames = empty($newEvents)? array() : explode(',', $newEvents);
            foreach($newEventNames as $newEventName) {
                $obj = $this->modx->getObject('modEvent', array('name' => $newEventName));
                if (!$obj) {
                    $obj = $this->modx->newObject('modEvent');
                    {
                        $obj->set('name', $newEventName);
                        $obj->set('groupname', $this->props['category']);
                        $obj->set('service', 1);

                        if ($obj && $obj->save()) {
                            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Created new System Event name: ' . $newEventName);
                        } else {
                            $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating System Event name: Could not save  ' . $newEventName);
                        }
                    }
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $newEventName . ': System Event name already exists');
                }
            }
        }


        if (empty($values)) {
            $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': value array is empty');
            return;
        }
        foreach ($values as $mainObjectName => $subsidiaryObjectNames) {
            if (empty($mainObjectName)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': main object name is empty');
                continue;
            }

            $alias = $this->getNameAlias($mainObjectType);
            if ($mainObjectType == 'modTemplate' && ($mainObjectName == 'default' || $mainObjectName == 'Default')) {
                $defaultTemplateId = $this->modx->getOption('default_template');
                $mainObject = $this->modx->getObject('modTemplate', $defaultTemplateId);
            } else {
                $mainObject = $this->modx->getObject($mainObjectType, array($alias => $mainObjectName) );
            }
            if (! $mainObject) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get main object ' . $mainObjectName);
                continue;
            }
            $subsidiaryObjectNames = explode(',', $subsidiaryObjectNames);
            if (empty($subsidiaryObjectNames)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name list is empty');
                continue;
            }
            foreach ($subsidiaryObjectNames as $subsidiaryObjectName) {
                $priority = 0;
                if (empty($subsidiaryObjectName)) {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name is empty');
                    continue;
                }

                if (strstr($subsidiaryObjectName, ':')) {
                    $s = explode(':', $subsidiaryObjectName);
                    $subsidiaryObjectName = trim($s[0]);
                    $subsidiaryObjectType = trim($s[1]);
                    if ($intersectType == 'modPluginEvent') {
                        $priority = (integer) trim($s[1]);
                    }
                }
                $alias = $this->getNameAlias($subsidiaryObjectType);
                $subsidiaryObjectType = $intersectType == 'modPluginEvent' ? 'modEvent' : $subsidiaryObjectType;
                $subsidiaryObject = $this->modx->getObject($subsidiaryObjectType, array($alias => $subsidiaryObjectName));
                if (! $subsidiaryObject) {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get subsidiary object ' . $subsidiaryObjectName);
                    continue;
                }
                if ($mainObjectType == 'modTemplate' && $subsidiaryObjectType == 'modResource') {
                    /* @var $mainObject modTemplate */
                    /* @var $subsidiaryObject modResource */
                    if ($subsidiaryObject->get('template') != $mainObject->get('id')) {
                        $subsidiaryObject->set('template', $mainObject->get('id'));
                        if ($subsidiaryObject->save()) {
                            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Connected ' . $mainObjectName . ' Template to ' . $subsidiaryObjectName . ' Resource');
                        } else {
                            $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType);
                        }
                    } else {
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $mainObjectName . ' Template is already connected to ' . $subsidiaryObjectName . ' Resource');
                    }
                    continue;
                } else {
                    $fields = array(
                        $fieldName1 => $mainObject->get('id'),
                        $fieldName2 => $intersectType == 'modPluginEvent' ? $subsidiaryObjectName : $subsidiaryObject->get('id'),

                    );
                    $intersect = $this->modx->getObject($intersectType, $fields);
                    /* @var $intersect xPDOObject */
                    if (!$intersect) {
                        $intersect = $this->modx->newObject($intersectType);
                        $intersect->set($fieldName1, $mainObject->get('id'));
                        $intersect->set($fieldName2, $intersectType == 'modPluginEvent' ? $subsidiaryObjectName : $subsidiaryObject->get('id'));
                        if ($intersectType == 'modPluginEvent') {
                            $intersect->set('priority', $priority);
                        }
                        if ($intersectType == 'modElementPropertySet') {
                            $intersect->set('element_class', $subsidiaryObjectType);
                        }

                        if ($intersect && $intersect->save()) {
                            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Created intersect ' . ' for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                        } else {
                            $this->modx->log(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Failed to save intersect');
                        }
                    } else {
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    Intersect ' . $intersectType . ' already exists for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                    }

                }
            }
        }
    }
}
