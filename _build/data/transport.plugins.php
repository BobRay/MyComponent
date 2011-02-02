<?php
/**
 * MyComponent transport plugins
 * Copyright 2011 Your Name <you@yourdomain.com>
 * @author Your Name <you@yourdomain.com>
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
 * Description:  Array of plugin objects for MyComponent package
 * @package mycomponent
 * @subpackage build
 */

if (! function_exists('getPluginContent')) {
    function getpluginContent($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php','',$o);
        $o = str_replace('?>','',$o);
        $o = trim($o);
        return $o;
    }
}
$plugins = array();

$plugins[1]= $modx->newObject('modplugin');
$plugins[1]->fromArray(array(
    'id' => 1,
    'name' => 'MyPlugin1',
    'description' => 'MyPlugin1 for MyComponent.',
    'plugincode' => getPluginContent($sources['source_core'].'/elements/plugins/myplugin1.plugin.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.myplugin1.php';
$plugins[1]->setProperties($properties);
unset($properties);


$plugins[2]= $modx->newObject('modplugin');
$plugins[2]->fromArray(array(
    'id' => 2,
    'name' => 'MyPlugin2',
    'description' => 'MyPlugin2 for MyComponent.',
    'plugincode' => getPluginContent($sources['source_core'].'/elements/plugins/myplugin2.plugin.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.myplugin2.php';
$plugins[2]->setProperties($properties);
unset($properties);

return $plugins;