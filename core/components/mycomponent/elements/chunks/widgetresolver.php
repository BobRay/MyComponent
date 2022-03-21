<?php
/**
* Resolver to connect widgets to system events for [[+packageName]] extra
*
* Copyright [[+copyright]] [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
* @subpackage build
*/
/* @var $object xPDOObject */
/* @var $widgetObj modDashboardWidget */
/* @var xPDOObject $object */
/* @var array $options */
/* @var $modx modX */
/* @var $widgetObj modDashboardWidget */
/* @var $widgetPlacement modDashboardWidgetPlacement */
/* @var $dashboard modDashboard */

if (!function_exists('checkFields')) {
    function checkFields($required, $objectFields) {

        global $modx;
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field])) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Widget Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
}


if ($object->xpdo) {
    $modx =& $object->xpdo;

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
                    if (!checkFields('widget,dashboard', $fields)) {
                        continue;
                    }
                    $widget = $modx->getObject($classPrefix . 'modDashboardWidget', array('name' => $fields['widget']));

                    $dashboard = $modx->getObject($classPrefix . 'modDashboard', (int) $fields['dashboard']);

                    if (!$widget || !$dashboard) {
                        if (!$widget) {
                            $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Widget  ' .
                                $fields['widget']);
                        }
                        if (!$dashboard) {
                            $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find dashboard with ID ' .
                                $fields['dashboard']);
                        }
                        continue;
                    }
                    $widgetPlacement = $modx->getObject($classPrefix . 'modDashboardWidgetPlacement',
                        array(
                            'widget'=>$widget->get('id'),
                            'dashboard' => (int)$fields['dashboard'],
                            )
                    );
                    
                    if (!$widgetPlacement) {
                        $widgetPlacement = $modx->newObject($classPrefix . 'modDashboardWidgetPlacement');
                    }
                    if ($widgetPlacement) {
                        $fields['rank'] = isset($fields['rank']) ? (int) $fields['rank'] : 0;
                        $widgetPlacement->set('widget', (int) $widget->get('id'));
                        $widgetPlacement->set('dashboard', (int) $fields['dashboard']);
                        $widgetPlacement->set('rank', (int)$fields['dashboard']);

                    }
                    if (! $widgetPlacement->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error saving widgetPlacement for ' .
                            $fields['widget'] . ' - ' . $fields['event']);
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:

            break;
    }
}

return true;