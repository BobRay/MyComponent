<?php
/**
* Resolver to connect TVs to templates for [[+packageName]] extra
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
        if (!isset($objectFields[$field])) {
            return false;
        }
    }
    return true;
}

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            $intersects = '[[+intersects]]';

            if (is_array($intersects)) {
                foreach ($intersects as $k => $fields) {
                    /* make sure we have all fields */
                    if (!checkFields('tmplvarid,templateid', $fields)) {
                        continue;
                    }
                    $tv = $modx->getObject('modTemplateVar', array('name' => $fields['tmplvarid']));
                    $template = $modx->getObject('modTemplate', array('templatename' => $fields['templateid']));
                    if (!$tv || !$template) {
                        continue;
                    }
                    $tvt = $modx->newObject('modTemplateVarTemplate');
                    if ($tvt) {
                        $tvt->set('tmplvarid', $tv->get('id'));
                        $tvt->set('templateid', $template->get('id'));
                        if (isset($fields['rank'])) {
                            $tvt->set('rank', $fields['rank']);
                        } else {
                            $tvt->set('rank', 0);
                        }
                    }

                    $tvt->save();
                }

            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;