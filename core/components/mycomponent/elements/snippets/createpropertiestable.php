<?php
/**
 * CreatePropertiesTable
 * Copyright 2012-2025 Bob Ray
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
 * @author Bob Ray <https://bobsguides.com>
 *
 * Description: The CreatePropertiesTable snippet creates a table of
 * properties to paste into tutorials and documentation.
 * The table is based on the properties in a properties file
 * and on a properties language file to pull descriptions from.
 */

$name = 'Snippet2';
$base = "c:/xampp/htdocs/addons/assets/mycomponents/{$name}/";
$propertiesFile = "_build/data/properties/properties.{$name}.snippet.php";
$propertiesFile = $base . $propertiesFile;
$languageFile = $base . "core/components/{$name}/lexicon/en/default.inc.php";
$languageFile = $base . "core/components/{$name}/lexicon/en/properties.inc.php";
$rewriteCodeFile = false;
$codeFile = $base . "core/components/{$name}/elements/snippets/{$name}.snippet.php";


$propertiesInjected = false; /* This will be set automatically if properties are injected */

if (php_sapi_name() != 'cli') {
    echo "<pre>\n"; /* used for nice formatting for log messages  */
    if (defined('MODX_CORE_PATH')) {
        if (!$modx->user->hasSessionContext('mgr')) {
            die ('Unauthorized Access');
        }
    }
}

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
        $pattern = '/(.+)[^_][Dd]efault[:\s](.+)$/';
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

$areas = array();
foreach ($properties as $property) {
    if (! in_array($property['area'], $areas) && !empty($property['area'])) {
        $areas[] = $property['area'];
    }
}

// print_r($areas, true);
if (empty($areas)){
    $areas = array('1' => '');
}


reset($properties);

foreach ($areas as $area) {
    if (!empty($area)) {
        $propertiesComment .= "\n           AREA: " . $area . "\n\n";
    }
    $rows .= '<tr><td colspan="3" class="properties_header">' . $area . '</td></tr>';
    foreach ($properties as $property) {
        if ($property['area'] !== $area) {
            continue;
        }
        $fields = array();

        $replaceFields = array(
            'name' => $property['name'],
            'description' => $property['desc'],
            'default' => $property['value'],
        );
        $isYesNo = $property['type'] == 'combo-boolean' ? true : false;
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

        if ($isYesNo) {
            $replaceFields['default'] = $replaceFields['default'] == '1' ||
            $replaceFields['default'] == 'yes' ||
            $replaceFields['default'] == 'YES' ? '1' : '0';
        }

        /* Wrap in <fixedpre> if more than 5 tags in default value */
        $needle = '<>';
        $haystack = $replaceFields['default'];
        $count = strlen($haystack) - strlen(str_replace(str_split($needle), '', $haystack));
        if ($count > 8) {
            $haystack = str_replace('>', '>,', $haystack);
            $haystack= htmlentities($haystack);
        }

        $commaCount = substr_count($haystack, ',');
        if ($commaCount > 5) {
            $haystack = str_replace(',', ',<wbr>', $haystack);
        }

        $replaceFields['default'] = $haystack;


        $row = str_replace($findFields, $replaceFields, $rowTpl);

        $rows .= $row;

        /* add to properties comment */

        $propertiesComment .= ' * @property &' . $property['name'] . ' ' . $property['type'];
        $propertiesComment .= ' -- ' . $fields['desc'] . '; Default: ';
        $propertiesComment .= empty($fields['default']) ? '(empty).' : $fields['default'];
        $propertiesComment = rtrim($propertiesComment, '.');
        $propertiesComment .= ".\n";
    }
}

$output = str_replace('[[+rows]]', $rows, $tableTpl);
if ($rewriteCodeFile && !empty($codeFile) ) {
    $content = file_get_contents($codeFile);
    $count = 0;
    if (!empty($content) && !empty($propertiesComment)) {
        // $propertiesComment = wrapComment($propertiesComment);
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
$output .= "\n\n/* Properties\n";
$output .= wrapComment($propertiesComment);
$output .= "\n */\n";
if ($propertiesInjected) {
    $output .=  "\n\n Properties injected into code file\n\nScroll up for table and properties comment";
} else {
    $output .=  "\n\n" . "No properties tag or multiple properties tags\nFile unchanged\nScroll up for table and properties comment\n\n";

}
echo $output;
//echo print_r($_lang);

return '';
