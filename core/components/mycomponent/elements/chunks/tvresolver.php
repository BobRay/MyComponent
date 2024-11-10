<?php
/**
* Resolver to connect TVs to templates for [[+packageName]] extra
*
* Copyright [[+copyright]] [[+author]] [[+email]]
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
    function checkFields($modx, $required, $objectFields) {
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field])) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[TV Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
}

/** @var modTransportPackage $transport */

if ($transport) {
    $modx =& $transport->xpdo;
} else {
    $modx =& $object->xpdo;
}



$classPrefix = $modx->getVersionData()['version'] >= 3
        ? 'MODX\Revolution\\'
        : '';

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        $intersects = '[[+intersects]]';

        if (is_array($intersects)) {
            foreach ($intersects as $k => $fields) {
                /* make sure we have all fields */
                if (!checkFields($modx, 'tmplvarid,templateid', $fields)) {
                    continue;
                }
                $tv = $modx->getObject($classPrefix . 'modTemplateVar', array('name' => $fields['tmplvarid']));
                if ($fields['templateid'] == 'default') {
                    $template = $modx->getObject($classPrefix . 'modTemplate', $modx->getOption('default_template'));
                } else {
                    $template = $modx->getObject($classPrefix . 'modTemplate', array('templatename' => $fields['templateid']));
                }
                if (!$tv || !$template) {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Template and/or TV ' .
                        $fields['templateid'] . ' - ' . $fields['tmplvarid']);
                    continue;
                }
                $tvt = $modx->getObject($classPrefix . 'modTemplateVarTemplate', array('templateid' => $template->get('id'), 'tmplvarid' => $tv->get('id')));
                if (! $tvt) {
                    $tvt = $modx->newObject($classPrefix . 'modTemplateVarTemplate');
                }
                if ($tvt) {
                    $tvt->set('tmplvarid', $tv->get('id'));
                    $tvt->set('templateid', $template->get('id'));
                    if (isset($fields['rank'])) {
                        $tvt->set('rank', $fields['rank']);
                    } else {
                        $tvt->set('rank', 0);
                    }
                    if (!$tvt->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error creating templateVarTemplate for ' .
                            $fields['templateid'] . ' - ' . $fields['tmplvarid']);
                    }
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error creating templateVarTemplate for ' .
                        $fields['templateid'] . ' - ' . $fields['tmplvarid']);
                }


            }

        }
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return true;
