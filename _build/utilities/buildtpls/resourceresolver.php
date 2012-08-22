<?php
/**
* Resolver to connect Resources to Templates for [[+packageName]] extra
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
/* @var $resourceObj modResource */
/* @var $templateObj modTemplate */

/* @var array $options */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /* [[+code]] */
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;