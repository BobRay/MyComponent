<?php

/**
 * MyComponent resolver script - runs on install.
 *
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
 * Description: Resolver script for MyComponent package
 * @package mycomponent
 * @subpackage build
 */

/* Example Resolver script */
$hasPlugins = true;
$hasTemplates = true;
$category = 'MyComponent';

$success = true;
$object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Running PHP Resolver.');
switch($options[xPDOTransport::PACKAGE_ACTION]) {
    /* This code will execute during an install */
    case xPDOTransport::ACTION_INSTALL:
        /* Assign plugins to category */
        if ($hasPlugins) {
            $plugin = $modx->getObject('modPlugin',array('name'=>'myplugin1.plugin.php'));
            $categoryObj = $modx->getObject('modCategory', array('category'=>$category));
            if ($categoryObj && $plugin) {
                $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Assigning Plugin myplugin1.plugin.php to Category: ' . $category);
                $plugin->set('category',$categoryObj->get('id'));
            } else {
                $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'Could not assigning Plugin myplugin1.plugin.php to Category: ' . $category);
                $success=false;
            }
            $plugin = $modx->getObject('modPlugin',array('name'=>'myplugin2.plugin.php'));
            if ($categoryObj && $plugin) {
                $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Assigning Plugin myplugin2.plugin.php to Category: ' . $category);
                $plugin->set('category',$categoryObj->get('id'));
            } else {
                $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'Could not assign Plugin myplugin2.plugin.php to Category: ' . $category);
                $success=false;
            }
        }

        break;

    /* This code will execute during an upgrade */
    case xPDOTransport::ACTION_UPGRADE:

        /* put any upgrade tasks (if any) here such as removing
           obsolete files, settings, elements, resources, etc.
        */

        $success = true;
        break;

    /* This code will execute during an uninstall */
    case xPDOTransport::ACTION_UNINSTALL:
        $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Uninstalling . . .');
        $success = true;
        break;

}
$object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Script resolver actions completed');
return $success;