<?php
/**
 * templates transport file for MyComponent extra
 *
 * Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
 * Created on 10-31-2012
 *
 * @package mycomponent
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $templates */


$templates = array();

$templates[1] = $modx->newObject('modTemplate');
$templates[1]->fromArray(array(
    'id' => '1',
    'templatename' => 'MyComponentTemplate',
    'properties' => '',
), '', true, true);
$templates[1]->setContent(file_get_contents($sources['source_core'] . '/elements/templates/mycomponenttemplate.html'));

return $templates;
