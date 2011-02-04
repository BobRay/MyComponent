<?php
/**
* Template variable objects for the MyComponents package
* @author Your Name <you@yourdomain.com>
* 1/1/11
*
* @package mycomponents
* @subpackage build
*/

/* Common 'type' options:
 * textfield (text line)
 * textbox
 * richtext
 * textarea
 * textareamini
 * email
 * html
 * image
 * date
 * option (radio buttons)
 * listbox
 * listbox-multiple
 * number
 * checkbox
 * tag
 * hidden
 */

/* Example template variables */

$templateVariables = array();

$templateVariables[1]= $modx->newObject('modTemplateVar');
$templateVariables[1]->fromArray(array(
    'id' => 1,
    'type' => 'textfield',
    'name' => 'MyTv1',
    'caption' => 'MyTv1',
    'description' => 'My template variable 1',
    'display' => 'default',
    'elements' => '',  /* input option values */
    'locked' => 0,
    'rank' => 0,
    'display_params' => '',
    'default_text' => 'Tv1 Default value',
    'properties' => array(),
),'',true,true);

$templateVariables[2]= $modx->newObject('modTemplateVar');
$templateVariables[2]->fromArray(array(
    'id' => 2,
    'type' => 'checkbox',
    'name' => 'MyTv2',
    'caption' => 'MyTv2',
    'description' => 'My template variable 2',
    'display' => 'default',
    'elements' => 'red||blue||green',  /* input option values */
    'locked' => 0,
    'rank' => 0,
    'display_params' => '',
    'default_text' => 'blue',
    'properties' => array(),
),'',true,true);

$templateVariables[3]= $modx->newObject('modTemplateVar');
$templateVariables[3]->fromArray(array(
    'id' => 3,
    'type' => 'option',
    'name' => 'MyTv3',
    'caption' => 'MyTv3',
    'description' => 'My template variable 3',
    'display' => 'default',
    'elements' => 'red||blue||green',  /* input option values */
    'locked' => 0,
    'rank' => 0,
    'display_params' => '',
    'default_text' => 'green',
    'properties' => array(),
),'',true,true);

return $templateVariables;
