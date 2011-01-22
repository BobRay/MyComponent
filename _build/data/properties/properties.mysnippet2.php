<?php

/**
 * Default properties for the MyComponent snippet
 * @author YourName <you@yourdomain.com>
 * 1/15/11
 *
 * @package mycomponent
 * @subpackage build
 */

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
        'value' => 'Some other text',
        'lexicon' => 'mycomponent:properties',
    ),

);

return $properties;