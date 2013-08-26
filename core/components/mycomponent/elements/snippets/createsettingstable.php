<?php
/**
 * CreateSettingsTable
 * Copyright 2012-2013 Bob Ray
 *
 * CreateSettingsTable is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * CreateSettingsTable is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CreateSettingsTable; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Bob Ray <http://bobsguides.com>

 *
 * Description: The CreateSettingsTable snippet creates a table of
 * System Settings and descriptions to paste into tutorials and documentation.
 * The table is based on the settings in a transport.settings.php file
 * and on a default.inc.php language file to pull descriptions from.
 * /

/*
  Modified: June, 2012
  $packageName = 'mycomponent';
*/


if ( (! isset($scriptProperties)) || empty($scriptProperties)) {
    $scriptProperties = array();
}

/* @var $modx modX */

/* config file must be retrieved in a class */
if (!class_exists('SettingsHelper')) {
    class SettingsHelper {

        public function __construct(&$modx) {
            /* @var $modx modX */
            $this->modx =& $modx;

        }

        public function getProps($configPath) {
            $properties = include $configPath;
            return $properties;
        }

        public function sendLog($level, $message) {

            $msg = '';
            if ($level == modX::LOG_LEVEL_ERROR) {
                $msg .= $this->modx->lexicon('mc_error')
                    . ' -- ';
            }
            $msg .= $message;
            $msg .= "\n";
            if (php_sapi_name() != 'cli') {
                $msg = nl2br($msg);
            }
            echo $msg;
        }
    }
}

/* Instantiate MODx -- if this require fails, check your
 * _build/build.config.php file
 */
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error', 'error.modError', '', '');
$modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE
    ? 'ECHO'
    : 'HTML');

if (!defined('MODX_CORE_PATH')) {
    session_write_close();
    die('build.config.php is not correct');
}

include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/config/current.project.php';

if (!$currentProject) {
    session_write_close();
    die('Could not get current project');
}

$helper = new SettingsHelper($modx);

$modx->lexicon->load('mycomponent:default');
$projectConfigPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/config/' . $currentProject . '.config.php';
$props = $helper->getProps($projectConfigPath);

if (!is_array($props)) {
    session_write_close();
    die($modx->lexicon('mc_no_config_file'));
}

$criticalSettings = array(
    'packageNameLower',
    'packageName',
    'version',
    'release',
    'targetRoot',
);

foreach ($criticalSettings as $setting) {
    if (!isset($setting)) {
        session_write_close();
        die($modx->lexicon('mc_critical_setting_not_set')
            . ': ' . $setting);
    }
}


$settingsInjected = false; /* This will be set automatically if settings are injected */


if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}


/* Properties sent in method call will override those in Project Config file */
$properties = array_merge($props, $scriptProperties);
$packageNameLower = $properties['packageNameLower'];

$myComponentRoot = isset($properties['mycomponentRoot'])
    ? $properties['mycomponentRoot']
    : '';
if (empty($myComponentRoot)) {
    session_write_close();
    die('myComponentRoot is not set in Project Config: ' . $projectConfigPath);
}
if (!is_dir($myComponentRoot)) {
    session_write_close();
    die('myComponentRoot set in project config is not a directory: ' . $projectConfigPath);
}
$mcRoot = $modx->getOption('mc.root', null,
    $modx->getOption('core_path') . 'components/mycomponent/');

$targetRoot = $modx->getOption('targetRoot', $properties, '');

if (empty($targetRoot)) {
    session_write_close();
    die('targetRoot is not set in project config file');
}
$props = $properties;

$settingsFile = $targetRoot . '_build/data/transport.settings.php';

$languageFile = $targetRoot . 'core/components/' . $packageNameLower . '/lexicon/en/default.inc.php';
$rewriteCodeFile = $modx->getOption('rewriteCodeFiles', $props, false);
$codeFile = $settingsFile;



if (!file_exists($settingsFile)) {
    echo 'Could not find settings file';
}
if (!file_exists($languageFile)) {
    echo 'Could not get language file';
}
$settings = include $settingsFile;
if (empty($settings)) {
    return 'No settings';
}

include $languageFile;
if (empty($_lang)) {
    return 'No language strings';
}

$tableTpl = "\n\n<table class=\"properties\">
    <tr><th>Setting</th><th>Description</th><th>Default</th></tr>
[[+rows]]
</table>";

$rowTpl = '
    <tr>
        <td>[[+name]]</td>
        <td>[[+description]]</td>
        <td>[[+default]]</td>
    </tr>';


$setting = array();
$findFields = array(
    '[[+name]]',
    '[[+description]]',
    '[[+default]]',
);
$rows = '';

/* wrap long lines in comment block */
function wrapComment($text, $width = 70) {
    $textArray = explode("\n",$text);
    foreach ($textArray as $k => $v) {
        $textArray[$k] = wordwrap ( $v , $width,"\n *    ");
    }
    return implode("\n", $textArray);


}
function parseDesc($text, &$fields) {
    // echo "\nTEXT: " . $text . "\n";
    $fields = array();
    $matches = array();
    if (isset($_lang[$text])) {
       $text = $_lang[$text];
    }

    if (strstr($text,'~~') ) {

        preg_match("/~~(.+)$/",$text,$matches);
        $text = $matches[1];
        //echo "\nTEXT: " . $text . "\n";
    }
    /* ~~ and prior text is now removed */
    /* get default and remove it from description */
    if (stristr($text,'default')) {
        // echo "\nTEXT: " . $text . "\n";
        $pattern = '/(.+)[Dd]efault[:\s](.+)$/';
        preg_match($pattern, $text, $matches);
        $fields['desc'] = $matches[1];

        $fields['default'] = $matches[2];
    } else {
        $fields['desc'] = $text;
    }
    $fields['desc'] = trim($fields['desc'],"\.\'\"\;\: ");

    if (isset($fields['default'])) {
        $fields['default'] = rtrim($fields['default'],"\.\'\"\;\: ");
        $fields['default'] = ltrim($fields['default'],"\'\"\;\: ");
    }
    //echo "DESCRIPTION: " . $fields['desc'] . "\n";
    // echo "DEFAULT: " . $fields['default'] . "\n";
}

//echo "COUNT: " . count($settings) . "\n";
$settingsComment = '';
foreach($settings as $settingObject) {
    /* @var $settingObject modSystemSetting */
    $setting = $settingObject->toArray();
    $fields = array();

    $replaceFields = array(
        'name' => $setting['key'],
        'description' => '',
        'default' => $setting['value'],
    );

    $replaceFields['default'] = $replaceFields['default'] == '999'? '(set automatically)' : $replaceFields['default'];
    $languageKey = 'setting_' . $replaceFields['name'] . '_desc';
    if (isset($_lang[$languageKey])) {
        $replaceFields['description'] = $_lang[$languageKey];
    }
    parseDesc($replaceFields['description'], $fields);
    if (isset($fields['desc']) && !empty($fields['desc'])) {
        $replaceFields['description'] = $fields['desc'];
    }
    if (isset($fields['default']) && empty($replaceFields['default'])) {
            $replaceFields['default'] = $fields['default'];
    }
    $replaceFields['default'] = $replaceFields['default'] === false? 'false' : $replaceFields['default'];
    $replaceFields['default'] = $replaceFields['default'] === true
        ? 'true'
        : $replaceFields['default'];
    $row = str_replace($findFields,$replaceFields,$rowTpl);

    $rows .= $row;

    /* add to properties comment */

    $settingsComment .= ' * @property &' . $setting['key'] . ' ' . $setting['xtype'];
    $settingsComment .= ' -- ' . $fields['desc'] . '; Default: ';
    $settingsComment .= empty($fields['default'])? '(empty).' : $fields['default'];
    $settingsComment .= ".\n";


}

$output = str_replace('[[+rows]]', $rows, $tableTpl);
if ($rewriteCodeFile && !empty($codeFile) ) {
    $content = file_get_contents($codeFile);
    $count = 0;
    if (!empty($content) && !empty($settingsComment)) {
        $settingsComment = wrapComment($settingsComment);
        $content = str_replace('[[+properties]]', "Properties:\n" . $settingsComment, $content, $count);
        /* write settings to transport file; this should only happen if they're
         * not there already */
        if ($count == 1) {

            $fp = fopen($codeFile, 'w');
            fwrite($fp, $content);
            fclose($fp);
            $settingsInjected = true;//echo $content;
        } else {
            $settingsInjected = false;
        }
    }
}
$output .= "\n\n/* Properties\n\n";
$output .= $settingsComment;
$output .= "\n */\n";
if ($settingsInjected) {
    $output .=  "\n\n Properties injected into code file\n\nScroll up for table and properties comment";
} else {
    $output .=  "\n\n" . "No properties tag or multiple properties tags\nFile unchanged\nScroll up for table and properties comment\n\n";

}
echo $output;
//echo print_r($_lang);

return '';


