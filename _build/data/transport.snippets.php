<?php
/**
 * MyComponent transport snippets
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
 * Description:  Array of snippet objects for MyComponent package
 * @package mycomponent
 * @subpackage build
 */

if (! function_exists('getSnippetContent')) {
    function getSnippetContent($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php','',$o);
        $o = str_replace('?>','',$o);
        $o = trim($o);
        return $o;
    }
}
$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'MySnippet1',
    'description' => 'MySnippet1 for MyComponent.',
    'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/mysnippet1.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'/properties/properties.mysnippet1.php';
$snippets[1]->setProperties($properties);
unset($properties);


$snippets[2]= $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => 2,
    'name' => 'MySnippet2',
    'description' => 'MySnippet2 for MyComponent.',
    'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/mysnippet2.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'/properties/properties.mysnippet2.php';
$snippets[2]->setProperties($properties);
unset($properties);

return $snippets;