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
    function checkFields($modx, $required, $objectFields) {
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

/* @var modTransportPackage $transport */

if ($transport) {
    $modx =& $transport->xpdo;
} else {
    $modx =& $object->xpdo;
}
$isMODX3 = $modx->getVersionData()['version'] >= 3;
$classPrefix = $isMODX3
        ? 'MODX\Revolution\\'
        : '';

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        $intersects = '[[+intersects]]';

        if (is_array($intersects) && !empty($intersects)) {
            foreach ($intersects as $k => $fields) { // each pass is one widget
                /* make sure we have all fields */
                $dashboardName = $modx->getOption('dashboard', $fields, 1, true);

                if (!checkFields($modx, 'widget,dashboard', $fields)) {
                    continue;
                }

                /* Get both objects (widget and dashboard) */
                $widget = $modx->getObject($classPrefix . 'modDashboardWidget', array('name' => $fields['widget']));
                $dashboardObject = $modx->getObject($classPrefix . 'modDashboard', array('name' => $dashboardName));

                $dashboardId = $dashboardObject->get('id');

                /* Set remaining placement fields (except 'user' -- only used for MODX 3+ */
                $rank = $modx->getOption('rank', $fields, 0, true);
                $size = $modx->getOption('size', $fields, 'half', true);

                /* Make sure we have both objects */
                if (!$widget || !$dashboardObject) {
                    if (!$widget) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Widget  ' .
                            $fields['widget']);
                    }
                    if (!$dashboardObject) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find dashboard with name ' .
                            $dashboardName);
                    }
                    continue;
                }

                $widgetId = $widget->get('id');

                $placementFields = array(
                    'widget' => $widgetId,
                    'dashboard' => $dashboardId,
                    'rank' => $rank,
                    'size' => $size,
                    'user' => 0,
                );

                if (! $isMODX3) {
                    unset($placementFields['user'], $placementFields['size']);
                }

                $widgetPlacement = $modx->getObject($classPrefix . 'modDashboardWidgetPlacement',
                    array(
                        'widget' => $widgetId,
                        'dashboard' => $dashboardId,
                    )
                );
                /* Create Placement if not there */
                if (!$widgetPlacement) {
                    $widgetPlacement = $modx->newObject($classPrefix . 'modDashboardWidgetPlacement');
                }
                if ($widgetPlacement) {
                    $widgetPlacement->fromArray($placementFields, '', true);
                    if (! $widgetPlacement->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR,
                            'Unknown error saving widgetPlacement for ' .
                            $fields['widget'] . ' Widget');
                    }
                }

            } /* End of foreach widget */
        } /* End of if ($intersects) */
        break;

    case xPDOTransport::ACTION_UNINSTALL:

        break;
}


return true;
