<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 6/2/12
 * Time: 11:47 PM
 * To change this template use File | Settings | File Templates.
 */

$packageName = 'Notify';
$packageNameLower = strtolower($packageName);
$base = 'c:/xampp/htdocs/addons/assets/mycomponents/notify/';
$codeFile = 'core/components/notify/model/notify/notify.class.php';
$codeFile = $base . $codeFile;
$propertiesFile = '_build/data/properties/properties.notify.php';
$propertiesFile = $base . $propertiesFile;
/* set name of properties array so we skip system setting requests and
 * local array searches. Could be $scriptProperties
*/
$propsName = '$this->props';

/* escape the $ */
$propsName = str_replace('$','\$',$propsName);

if (!file_exists($propertiesFile)) {
    echo 'Could not find properties file';
}
if (!file_exists($codeFile)) {
    echo 'Could not get code file';
}
$properties = require $propertiesFile;
$code = file_get_contents($codeFile);
//echo "\nCOUNT: " . count($properties) . " properties in properties file\n";
$propertyTpl = "
    array(
        'name' => '[[+name]]',
        'desc' => '[[+name]]_desc~~(optional)Add description here for LexiconHelper',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => '[[+package_name_lower]]:properties',
        'area' => '',
    ),
";


$matches = array();

$pattern = "/getOption\(\'([^\\']+)\'.+" . $propsName . "/";

//echo 'PATTERN: ' . $pattern . "\n";
preg_match_all($pattern, $code, $matches);
//echo "\nCOUNT: " . count($maches[1]) . " properties in code file\n";
echo "Properties in code file\n********************\n";
foreach($matches[1] as $prop) {
    echo $prop . "\n";
}

$names = array();
$missing = array();
foreach($properties as $property) {
  $names[] = $property['name'];
}
$orphans = array();
foreach( $names as $name) {
    if (! in_array($name, $matches[1])) {
        $orphans[] = $name;
    }
}
foreach($matches[1] as $k => $v) {
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

echo "\nOrphans in properties file (may be used in another file)\n********************\n";

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