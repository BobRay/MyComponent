<?php
/**
 * Validator for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 * @package [[+packageNameLower]]
 * @subpackage build
 */

/* @var $object xPDOObject */
/* @var $tvObj modTemplateVar */
/* @var $templateObj modTemplate */
/* @var $tvt modTemplateVarTemplate */

/* @var array $options */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            /* return false if conditions are not met */

            /* [[+code]] */
            break;
        case xPDOTransport::ACTION_UPGRADE:
            /* return false if conditions are not met */
            /* [[+code]] */
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;