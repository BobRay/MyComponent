<?php
/**
 * CreatePropertiesTable
 * Copyright 2012 Bob Ray
 *
 * CreatePropertiesTable is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * CreatePropertiesTable is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CreatePropertiesTable; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Bob Ray <http://bobsguides.com>

 *
 * Description: The CreatePropertiesTable snippet creates a table of
 * properties to paste into tutorials and documentation.
 * The table is based on the properties in a properties file
 * and on a properties language file to pull descriptions from.
 * /

/*
  Modified: June, 2012
  $packageName = 'mycomponent';
*/

$base = 'c:/xampp/htdocs/addons/assets/mycomponents/notify/';
$propertiesFile = '_build/data/properties/properties.notify.php';
$propertiesFile = $base . $propertiesFile;
$languageFile = 'core/components/notify/lexicon/en/properties.inc.php';
$languageFile = $base . $languageFile;
$rewriteCodeFile = false;
$codeFile = $base . 'core/components/notify/elements/snippets/notify.snippet.php';


$propertiesInjected = false; /* This will be set automatically if properties are injected */



if (!file_exists($propertiesFile)) {
    echo 'Could not find properties file';
}
if (!file_exists($languageFile)) {
    echo 'Could not get code file';
}
$properties = include $propertiesFile;
if (empty($properties)) {
    return 'No properties';
}

include $languageFile;
if (empty($_lang)) {
    return 'No language strings';
}

$tableTpl = "\n\n<table class=\"properties\">
    <tr><th>Property</th><th>Description</th><th>Default</th></tr>
[[+rows]]
</table>";

$rowTpl = '
    <tr>
        <td>[[+name]]</td>
        <td>[[+description]]</td>
        <td>[[+default]]</td>
    </tr>';


$property = array();
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
    //echo "DEFAULT: " . $fields['default'] . "\n";
}

//echo "COUNT: " . count($properties) . "\n";
$propertiesComment = '';
foreach($properties as $property) {
    $fields = array();

    $replaceFields = array(
        'name' => $property['name'],
        'description' => $property['desc'],
        'default' => $property['value'],
    );
    if (isset($_lang[$replaceFields['description']])) {
        $replaceFields['description'] = $_lang[$replaceFields['description']];
    }
    parseDesc($replaceFields['description'], $fields);
    if (isset($fields['desc']) && !empty($fields['desc'])) {
        $replaceFields['description'] = $fields['desc'];
    }
    if (isset($fields['default']) && empty($replaceFields['default'])) {
            $replaceFields['default'] = $fields['default'];
        }
    $row = str_replace($findFields,$replaceFields,$rowTpl);

    $rows .= $row;

    /* add to properties comment */

    $propertiesComment .= ' * @property &' . $property['name'] . ' ' . $property['type'];
    $propertiesComment .= ' -- ' . $fields['desc'] . '; Default: ';
    $propertiesComment .= empty($fields['default'])? '(empty).' : $fields['default'];
    $propertiesComment .= ".\n";


}

$output = str_replace('[[+rows]]', $rows, $tableTpl);
if ($rewriteCodeFile && !empty($codeFile) ) {
    $content = file_get_contents($codeFile);
    $count = 0;
    if (!empty($content) && !empty($propertiesComment)) {
        $propertiesComment = wrapComment($propertiesComment);
        $content = str_replace('[[+properties]]', "Properties:\n" . $propertiesComment, $content, $count);

        if ($count == 1) {

            $fp = fopen($codeFile, 'w');
            fwrite($fp, $content);
            fclose($fp);
            $propertiesInjected = true;//echo $content;
        } else {
            $propertiesInjected = false;
        }
    }
}
$output .= "\n\n/* Properties\n\n";
$output .= $propertiesComment;
$output .= "\n */\n";
if ($propertiesInjected) {
    $output .=  "\n\n Properties injected into code file\n\nScroll up for table and properties comment";
} else {
    $output .=  "\n\n" . "No properties tag or multiple properties tags\nFile unchanged\nScroll up for table and properties comment\n\n";

}
echo $output;
//echo print_r($_lang);

return '';


