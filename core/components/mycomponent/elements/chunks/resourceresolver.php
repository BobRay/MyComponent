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

function checkFields($required, $objectFields) {
    $fields = explode(',', $required);
    foreach ($fields as $field) {
        if (! isset($objectFields[$field])) {
            return false;
        }
    }
    return true;
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
                    if ($resource) {
                        continue;
                    }
                    $templateObj = $modx->getObject('modTemplate', array('templatename' => $fields['template']));
                    if ($templateObj) {
                        $resource->set('template', $templateObj->get('id'));
                    }
                    if ($fields['parent'] != 0) {

                        $parentObj = $modx->getObject('modResource', array('pagetitle' => $fields['parent']));
                        if ($parentObj) {
                            $resource->set('template', $parentObj->get('id'));
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