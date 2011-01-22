<?php

/**
 * Default properties for the MyComponent snippet
 * @author YourName <you@yourdomain.com>
 * 1/15/11
 *
 * @package mycomponent
 * @subpackage build
 */
/* ToDo: Add property1 property2 to search and replace list */
$properties = array(
    array(
        'name' => 'property1',
        'desc' => 'mc_property1_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => '1',
        'lexicon' => 'mycomponent:properties',
    ),
     array(
        'name' => 'property2',
        'desc' => 'mc_property2_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'Some Text',
        'lexicon' => 'mycomponent:properties',
    ),
    array(
        'name' => 'property3',
        'desc' => 'mc_property3_desc',
        'type' => 'list',
        'options' => array(
            array(
                'name' => 'System Default',
                'value' => 'System Default',
                'menu' => '',
            ),
            array(
                'name' => 'Yes',
                'value' => 'Yes',
                'menu' => '',
            ),
            array(
                'name' => 'No',
                'value' => 'No',
                'menu' => '',
            ),
            array(
                'name' => 'Parent',
                'value' => 'Parent',
                'menu' => '',
            ),
        ),
        'value' => 'System Default',
        'lexicon' => 'mycomponent:properties',
    ),
 );

return $properties;