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

/* The $modx object is not available here. In its place we
 * use $object->xpdo
 */

/* Connecting plugins to the appropriate system events and
 * connecting TVs to their templates is done here.
 *
 * You will have to hand-code the names of the elements and events
 * in the arrays below.
 */

$pluginEvents = array('OnBeforeUserFormSave','OnUserFormSave');
$plugins = array('MyPlugin1', 'MyPlugin2');
$templates = array('myTemplate1','myTemplate2');
$tvs = array('MyTv1','MyTv2');
$category = 'MyComponent';

$hasPlugins = true;
$hasTemplates = false;

$success = true;
$object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Running PHP Resolver.');
switch($options[xPDOTransport::PACKAGE_ACTION]) {
    /* This code will execute during an install */
    case xPDOTransport::ACTION_INSTALL:
        /* Assign plugins to System events */
        if ($hasPlugins) {
            foreach($plugins as $k => $plugin) {
                $pluginObj = $object->xpdo->getObject('modPlugin',array('name'=>$plugin));
                if (! $pluginObj) $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'cannot get object: ' . $plugin);
                if (empty($pluginEvents)) $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Cannot get System Events');
                if (!empty ($pluginEvents) && $pluginObj) {

                    $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Assigning Events to Plugin ' . $plugin);

                    foreach($pluginEvents as $k => $event) {
                        $intersect = $object->xpdo->newObject('modPluginEvent');
                        $intersect->set('event',$event);
                        $intersect->set('pluginid',$pluginObj->get('id'));
                        $intersect->save();
                    }
                }
            }
        }
        /* For some reason, adding the templates to the category doesn't
         * work, so we'll add them again here
         */

        if ($hasTemplates) {
            if (!empty($templates)) {
                $ok = true;
                foreach ($templates as $template) {
                    $template = $object->xpdo->getObject('modTemplate',array('templatename'=>$template));
                    $categoryObj = $object->xpdo->getObject('modCategory', array ('category'=>$category));
                    if ($categoryObj) {
                        $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Failed to retrieve category: ' . $category);

                    } else {
                        $categoryId = $categoryObj->get('id');
                    }

                    if (! $template->set('category',$categoryId)){
                        $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Failed to set category for template: ' . $template);
                        $ok = false;
                    };
                    if (! $template->save()) {
                        $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Failed to save template: ' . $template);
                        $ok = false;
                    }
                }
                if ($ok) {
                    $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'Template categories set successfully');
                }
            }
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_INFO,'No templates to operate on');
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