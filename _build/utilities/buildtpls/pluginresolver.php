<?php
/**
* Resolver to connect plugins to system events for [[+packageName]] extra
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
* @subpackage build
*/
/* @var $object xPDOObject */
/* @var $pluginObj modPlugin */
/* @var $mpe modPluginEvent */
/* @var xPDOObject $object */
/* @var array $options */
/* @var $modx modX */
/* @var $pluginObj modPlugin */
/* @var $pluginEvent modPluginEvent */
/* @var $obj modEvent */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            $newSystemEvents = '[[+newEvents]]';
            $eventNames = empty($eventNames)? array() : explode(',', $eventNames);
            foreach($eventNames as $eventName) {
                $obj = $modx->getObject('modEvent', array('name' => $eventName));
                if (! $obj) {
                    $obj = $modx->newObject('modEvent');
                    {
                        $obj->set('name', $eventName);
                        $obj->set('groupname', '[[+category]]');
                        $obj->set('service', 1);
                        $obj->save();
                    }
                }
            }

            /* [[+code]] */
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            /* [[+remove_new_events]] */
            break;
    }
}

return true;