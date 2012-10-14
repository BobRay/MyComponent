<?php
/**
 * Category resolver  for [[+packageName]] extra.
 * Sets Category Parent
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
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field]) || empty($objectFields[$field])) {
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
                    if (!checkFields('category,parent', $fields)) {
                        continue;
                    }
                    $categoryObj = $modx->getObject('modCategory', array('category' => $fields['category']));
                    if (!$category) {
                        continue;
                    }
                    $parentObj = $modx->getObject('modCategory', array('category' => $fields['parent']));
                        if ($templateObj) {
                            $categoryObj->set('parent', $parentObj->get('id'));
                        }
                    $categoryObj->save();
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;