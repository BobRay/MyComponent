<?php
/**
 * MyComponent transport chunks
 * Copyright 2011-2017 Bob Ray <https://bobsguides.com>
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
 * Description: Array of chunk objects for MyComponent package
 * @package mycomponent
 * @subpackage build
 */

$chunks = array();

$chunks[1]= $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
    'id' => 1,
    'name' => 'MyChunk1',
    'description' => 'MyChunk1 for MyComponent',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/mychunk1.chunk.tpl'),
    'properties' => '',
),'',true,true);

$chunks[2]= $modx->newObject('modChunk');
$chunks[2]->fromArray(array(
    'id' => 2,
    'name' => 'MyChunk2',
    'description' => 'MyChunk2 for entire MyComponent',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/mychunk2.chunk.tpl'),
    'properties' => '',
),'',true,true);

return $chunks;