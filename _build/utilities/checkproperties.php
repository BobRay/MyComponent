<?php
/**
 * CheckProperties Utility Script for My Component
 * @author Bob Ray
 * Copyright 2012 Bob Ray
 * Modified: July, 2012
 *
 * CheckProperties is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * CheckProperties is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CheckProperties; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Bob Ray <http://bobsguides.com>

 *
 * Description: The CheckProperties script identifies properties
 * used in code with $modx->getOption() or some version of $scriptProperties and checks 
 * them against properties in the properties file.
 *
 * Output can be pasted into the properties file.
 *
 * No files are altered.
 */


$prefix = 'np_'; /* prefix for Lexicon entries */
$packageName = 'newspublisher'; /* Important: match directory case */
$packageNameLower = strtolower(($packageName));
$elementName = 'newspublisher'; /* Match file/directory case */
$elementName = empty($elementName)? $packageName: $elementName;
$elementName = strtolower($elementName);

/* set name of properties array alias so we skip system setting requests and
 * local array searches. Could be $scriptProperties
*/
$propsAlias = '$this->props';
/* escape the $ */
$propsAlias = str_replace('$','\$',$propsAlias);


$base = 'c:/xampp/htdocs/addons/assets/mycomponents/' . $packageName .  '/';

/* array of possible code files. Add your own if necessary */
$codeFiles = array(
    $base . 'core/components/' . $packageName . '/model/' . $packageName . '/' . $elementName . '.class.php',
    $base . 'core/components/' . $packageName . '/elements/snippets/' . $elementName . '.snippet.php',
    $base . 'core/components/' . $packageName . '/elements/plugins/' . $elementName . '.plugin.php',
);

$code = '';

foreach ($codeFiles as $codeFile) {
    if (file_exists($codeFile) ) {
        $code .= file_get_contents($codeFile) . "\n\n";
    }
}
if (empty($code)) {
    die ('Could not find any code files');
}


/* Places to look for Properties file */
$propsBase = $base . '_build/';
$locations = array(
    $propsBase . 'data/properties/properties.' . $elementName . '.php',
    $propsBase . 'data/properties/properties.inc.php',
    $propsBase . 'data/properties.' . $elementName . '.php',
    $propsBase . 'data/properties.inc.php',
    $propsBase . 'properties.' . $elementName . 'inc.php',
    $propsBase . 'properties.inc.php',
);

$found = 0;
$propertiesFile = '';
foreach ($locations as $location) {
    if (file_exists($location)) {
        $found++;
        $propertiesFile = $location;
    }
}

if ($found == 0) {
    die('Could Not find Properties file');
}

if ($found > 1) {
    die('Found more than one Properties file');
}

$properties = require $propertiesFile;

//echo "\nCOUNT: " . count($properties) . " properties in properties file\n";
$propertyTpl = "
    array(
        'name' => '[[+name]]',
        'desc' => '{$prefix}[[+name]]_desc~~(optional)Add description here for LexiconHelper',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => '[[+package_name_lower]]:properties',
        'area' => '',
    ),
";


$matches = array();

//$pattern = "/getOption\(\'([^\\']+)\'.+" . $propsAlias . "/";
    $pattern = "/" . $propsAlias . "\[[\"\']([^\"\']+)/";
//echo 'PATTERN: ' . $pattern . "\n";
/* get properties used with $scriptProperties['propertyName'] */
preg_match_all($pattern, $code, $matches);

$codeMatches = $matches[1];

$matches = array();
/* get properties accessed with getOption() */
$pattern = "/getOption\(\'([^\\']+)\'.+" . $propsAlias . "/";
preg_match_all($pattern, $code, $matches);

$codeMatches = array_merge($codeMatches, $matches[1]);

$codeMatches = array_unique($codeMatches);

echo "\nCOUNT: " . count($codeMatches) . " properties in code file\n";
echo "Properties in code file\n********************\n";
foreach($codeMatches as $prop) {
    echo $prop . "\n";
}

/* $pattern = "/\" . $sp . "\[[\"\'](.+)[\'\"]\]/";
*/
$names = array();
$missing = array();
foreach($properties as $property) {
  $names[] = $property['name'];
}
$orphans = array();
foreach( $names as $name) {
    if (! in_array($name, $codeMatches)) {
        $orphans[] = $name;
    }
}
foreach($codeMatches as $k => $v) {
    if (! in_array($v, $names)) {
        $missing[] = $v;
    }
}

echo "\nMissing from properties file\n********************\n";
if (empty($missing)) {
    echo "None\n";
}else {
    foreach ($missing as $name) {
        echo $name . "\n";
    }
}

echo "\nOrphans in properties file (may be used in another file or generated dynamically)\n********************\n";

if (empty($orphans)) {
    echo "None\n";
} else {
    foreach ($orphans as $name) {
        echo $name . "\n";
    }
}


if (!empty($missing)) {
echo "\nProperties to paste into properties file\n(Note: assumes type=textfield -- values unset)\n********************\n\n";


    $propertyText = '';

    if (count($properties) ==1) {
        $propertyText .= "\$properties = array (
";
    }
    foreach ($missing as $propertyName) {
        $tempPropertyTpl = str_replace('[[+name]]', $propertyName, $propertyTpl);
        $tempPropertyTpl = str_replace('[[+package_name_lower]]', $packageNameLower, $tempPropertyTpl);
        $propertyText .= $tempPropertyTpl;
    }

    if (count($properties)==1) {
        $propertyText .= "
);
return \$properties;";
    }
    echo $propertyText . "\n\n";

}