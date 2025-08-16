<?php
/**
 * MyComponent transport templates
 * Copyright 2-11-2025 Bob Ray <https://bobsguides.com>
 * @author Bob Ray <https://bobsguides.com>
 * 1/1/11
 *
 * MyComponent is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */
/**
 * Description: Array of template objects for MyComponent package
 * @package mycomponent
 * @subpackage build
 */

$templates = array();

$templates[1]= $modx->newObject('modTemplate');
$templates[1]->fromArray(array(
    'id' => 1,
    'templatename' => 'myTemplate1',
    'description' => 'Template One for MyComponent package',
    'content' => file_get_contents($sources['source_core'].'/elements/templates/mytemplate1.tpl'),
    'properties' => '',
),'',true,true);

$templates[2]= $modx->newObject('modTemplate');
$templates[2]->fromArray(array(
    'id' => 2,
    'templatename' => 'myTemplate2',
    'description' => 'Template Two for MyComponent Package',
    'content' => file_get_contents($sources['source_core'].'/elements/templates/mytemplate2.tpl'),
    'properties' => '',
),'',true,true);

return $templates;
