<?php
/**
 * Validator for [[+packageName]] extra
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
/* @var array $options */

/** @var $transport modTransportPackage */
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


return true;
