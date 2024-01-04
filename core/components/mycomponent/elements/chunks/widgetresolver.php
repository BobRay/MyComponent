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
                $dashboardId = $modx->getOption('dashboard', $fields, 1, true);

                if (!checkFields($modx, 'widget,dashboard', $fields)) {
                    continue;
                }

                /* Get both objects (widget and dashboard) */
                $widget = $modx->getObject($classPrefix . 'modDashboardWidget', array('name' => $fields['widget']));
                $dashboardObject = $modx->getObject($classPrefix . 'modDashboard', $dashboardId);

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
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find dashboard with ID ' .
                            $dashboardId);
                    }
                    continue;
                }

                $widgetId = $widget->get('id');

                $placementFields = array(
                    'widget' => $widgetId,
                    'dashboard' => $dashboardId,
                    'rank' => $rank,
                    'size' => $size,
                );

                if ($isMODX3) {
                    /* Get dashboard's widget placement objects */
                    $placements = $dashboardObject->getMany('Placements');
                    $users = array();
                    $existing = array();
                    $elements = array(
                        'user',
                        'dashboard',
                        'widget',
                    );

                    /* Create $users array of userIds from placements, and $existing array
                       of signatures in the form userid:dashboardid:widgetid
                       from placements. */
                    foreach ($placements as $placement) {
                        $temp = array();
                        foreach ($elements as $element) {
                            $temp[] = $placement->get($element);
                        }

                        $existing[] = implode(':', $temp);
                        unset($temp);

                        $users[] = $placement->get('user');
                    }

                    /* Remove duplicate users */
                    $users = array_unique($users);

                    /* Create and save placement objects */
                    foreach ($users as $user) {
                        /* Check signature against existing ones */
                        $signature = $user . ':' . $dashboardId . ':' . $widgetId;
                        if (in_array($signature, $existing)) {
                            continue; /* Placement already exists */
                        }

                        /* Create and save placement */
                        $dbp = $modx->newObject('MODX\Revolution\modDashboardWidgetPlacement');
                        if ($dbp) {
                            /* Add current user to array */
                            $placementFields['user'] = $user;

                            /* Set placement fields */
                            $dbp->fromArray($placementFields, '', true);

                            if (!$dbp->save()) {
                                $modx->log(xPDO::LOG_LEVEL_ERROR,
                                    'Unknown error saving widgetPlacement for ' .
                                    $fields['widget'] . 'Widget');
                            }
                        }
                    }

                } else { /* MODX 2 */
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
                } /* end of MODX 2 section */
            } /* End of foreach widget */
        } /* End of if ($intersects) */
        break;

    case xPDOTransport::ACTION_UNINSTALL:

        break;
}


return true;
