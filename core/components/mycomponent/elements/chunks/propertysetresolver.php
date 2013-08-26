<?php
/**
* Resolver to connect Property Sets to Elements for [[+packageName]] extra
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
* @subpackage build
*/

/* @var $object xPDOObject */
/* @var $propertySetObj modPropertySet */
/* @var $elementObj modElement */
/* @var $elementPropertySet modElementPropertySet */

/* @var array $options */
if (!function_exists('getNameAlias')) {
    function getNameAlias($elementType)
    {
        switch ($elementType) {
            case 'modTemplate':
                $nameAlias = 'templatename';
                break;
            case 'modCategory':
                $nameAlias = 'category';
                break;
            case 'modResource':
                $nameAlias = 'pagetitle';
                break;
            default:
                $nameAlias = 'name';
                break;
        }
        return $nameAlias;

    }
}

if (!function_exists('checkFields')) {
    function checkFields($required, $objectFields) {
        global $modx;
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field])) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[PropertySet Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
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
                if (!checkFields('element,element_class,property_set', $fields)) {
                    continue;
                }
                $elementObj = $modx->getObject($fields['element_class'],
                    array(getNameAlias($fields['element_class']) => $fields['element']));

                $propertySetObj = $modx->getObject('modPropertySet', array('name' => $fields['property_set']));

                if (!$elementObj || !$propertySetObj) {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Element and/or Property Set ' .
                        $fields['element'] . ' - ' . $fields['property_set']);
                    continue;
                }
                $fields['element'] = $elementObj->get('id');
                $fields['property_set'] = $propertySetObj->get('id');

                $tvt = $modx->getObject('modElementPropertySet', $fields);
                if (!$tvt) {
                    $tvt = $modx->newObject('modElementPropertySet');
                }
                if ($tvt) {
                    foreach($fields as $key => $value) {
                        $tvt->set($key, $value);
                    }
                    if (!$tvt->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error creating elementPropertySet intersect for ' .
                            $fields['element'] . ' - ' . $fields['property_set']);
                    }

                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error creating elementPropertySet intersect for ' .
                        $fields['element'] . ' - ' . $fields['property_set']);
                }
            }
        }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;