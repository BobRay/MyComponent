<?php
/**
* Resource resolver  for MyComponent extra.
* Sets template, parent, and (optionally) TV values
*
* Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
* Created on 10-30-2012
*
 * MyComponent is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
* @package mycomponent
* @subpackage build
*/

/* @var $object xPDOObject */
/* @var $modx modX */
/* @var $parentObj modResource */
/* @var $templateObj modTemplate */

/* @var array $options */

if (!function_exists('checkFields')) {
    function checkFields($required, $objectFields) {
        global $modx;
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (! isset($objectFields[$field])) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Resource Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
}
if($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            $intersects = array (
                0 =>  array (
                  'pagetitle' => 'MyComponent',
                  'parent' => 'default',
                  'template' => 'MyComponentTemplate',
                ),
            );

            if (is_array($intersects)) {
                foreach ($intersects as $k => $fields) {
                    /* make sure we have all fields */
                    if (! checkFields('pagetitle,parent,template', $fields)) {
                        continue;
                    }
                    $resource = $modx->getObject('modResource', array('pagetitle' => $fields['pagetitle']));
                    if (! $resource) {
                        continue;
                    }
                    if ($fields['template'] == 'default') {
                        $resource->set('template', $modx->getOption('default_template'));
                    } else {
                        $templateObj = $modx->getObject('modTemplate', array('templatename' => $fields['template']));
                        if ($templateObj) {
                            $resource->set('template', $templateObj->get('id'));
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, '[Resource Resolver] Could not find template: ' . $fields['template']);
                        }
                    }
                    if (!empty($fields['parent'])) {
                        if ($fields['parent'] != 'default') {
                            $parentObj = $modx->getObject('modResource', array('pagetitle' => $fields['parent']));
                            if ($parentObj) {
                                $resource->set('parent', $parentObj->get('id'));
                            } else {
                                $modx->log(modX::LOG_LEVEL_ERROR, '[Resource Resolver] Could not find parent: ' . $fields['parent']);
                            }
                        }
                    }

                    if (isset($fields['tvValues'])) {
                        foreach($fields['tvValues'] as $tvName => $value) {
                            $resource->setTVValue($tvName, $value);
                        }

                    }
                    $resource->save();
                }

            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;