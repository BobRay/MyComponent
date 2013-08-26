<?php
/**
* Resource resolver  for [[+packageName]] extra.
* Sets template, parent, and (optionally) TV values
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
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

            $intersects = '[[+intersects]]';

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