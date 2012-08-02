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
/* @var $tvObj modTemplateVar */
/* @var $templateObj modTemplate */
/* @var $tvt modTemplateVarTemplate */

/* @var array $options */

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;
            $tvObj = null;
            $templateObj = null;
            $tvId = 0;
            $templateId = 0;
            /* [[+code]] */
            break;
    }
}

return true;