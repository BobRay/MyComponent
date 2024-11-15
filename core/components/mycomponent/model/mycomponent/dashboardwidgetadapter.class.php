<?php

/* $modx->lexicon->load('mycomponent:default'); */

class DashboardWidgetAdapter extends ObjectAdapter {
    protected $dbClass = 'modDashboardWidget';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'namespace';
    protected $myFields;

    final public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $helpers Helpers */

        parent::__construct($modx, $helpers);

        $this->name = $fields['name'];

        if (is_array($fields)) {
            if (!isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->getProp('packageNameLower');
            }
            if (isset($fields['dashboards'])) {
                $this->setWidgetResolver($fields, $mode);
                unset($fields['dashboards']);
            }

            $this->myFields = $fields;
            ObjectAdapter::$myObjects['widgets'][] = $fields;
            $this->myFields = $fields;

        }
    }

    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        $rank = $this->modx->getOption('rank', $this->myFields['rank'], 0);
        unset($fields['rank'], $fields['dashboard']);
        $dashboard = $this->modx->getOption('dashboard', $this->myFields['dashboard'], 1);
        $obj = $this->modx->getObject($this->classPrefix . 'modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
        if (! $obj) {
            $widget = $this->modx->newObject($this->classPrefix . 'modDashboardWidget');
            $widget->fromArray($fields, '', false, true);

            if ($widget->save()) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_widget')
                    . ': ' . $fields['name']);
                /*$widget = $this->modx->getObject($this->classPrefix . 'modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
                if ($widget) {
                    $id = $widget->get('id');
                    $widgetPlacement = $this->modx->getObject($this->classPrefix . 'modDashboardWidgetPlacement', array('dashboard'=> 1, 'widget' => $id));
                    if (! $widgetPlacement) {
                        $widgetPlacement = $this->modx->newObject($this->classPrefix . 'modDashboardWidgetPlacement');
                        $widgetPlacement->set('dashboard', $dashboard);
                        $widgetPlacement->set('widget', $id);
                        $widgetPlacement->set('rank', $rank);
                        $widgetPlacement->save();
                    }

                }*/
            }
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_widget_already_exists')
                    . ' ' . $fields['text']);
        }
    }

    public function setWidgetResolver($fields, $mode)
    {
        if ($mode == MODE_BOOTSTRAP) {
            // foreach($fields as $placement) {
              //   $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($fields, true) . ' --- ' . print_r($placement, true));
                foreach ($fields as $dashboard => $rank) {
                    $resolverFields = array();
                    $resolverFields['dashboard'] = $dashboard;
                    $resolverFields['widget'] = $this->getName();
                    $resolverFields['rank'] = isset($rank) && !empty($rank) ? $rank : '0';
                    ObjectAdapter::$myObjects['widgetResolver'][] = $resolverFields;
                }
            // }
        } elseif ($mode == MODE_EXPORT) {
            $me = $this->modx->getObject($this->classPrefix . 'modDashboardWidget', array('name' => $this->getName()));
            if (!$me) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[TemplateVar Adapter] ' .
                    $this->modx->lexicon('mc_self_nf'));
            } else {
                $placements = $me->getMany('Placements');
                if (!empty($placements)) {
                    foreach ($placements as $placement) {
                        /* @var $placement modDashboardWidgetPlacement */
                        $fields = $placement->toArray();
                        $widgetObj = $this->modx->getObject($this->classPrefix . 'modDashboardWidget',
                                $fields['widget']);
                        $widgetName = $widgetObj->get('name');

                        $resolverFields = array(
                            'widget' => $widgetName,
                            'dashboard' => $fields['dashboard'],
                            'rank' => $fields['rank'],
                            'size' => $fields['size'],
                        );
                        ObjectAdapter::$myObjects['widgetResolver'][] = $resolverFields;
                    }
                }
            }
        }

    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $prefix = $helpers->modx->getVersionData()['version'] >= 3
            ? 'MODX\Revolution\\'
            : '';

        $widgets = array();
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_widgets'));
        if ($mode == MODE_BOOTSTRAP) {
            $widgets = $helpers->modx->getOption('widgets', ObjectAdapter::$myObjects, array());
            foreach ($widgets as $widget => $fields) {
                unset($fields['dashboards']);
            }
        } elseif ($mode == MODE_EXPORT) {
            $namespaces = $helpers->modx->getOption('namespaces', $helpers->props, array());
            foreach ($namespaces as $namespace => $fields) {

                $name = isset($fields['name']) ? $fields['name'] : $namespace;
                $name = strtolower($name);
                $objects = $helpers->modx->getCollection( $prefix . 'modDashboardWidget', array('namespace' => $name));
                foreach($objects as $object) {
                    /** @var $object xPDOObject */
                    $fields = $object->toArray();
                    $widgets[] = $fields;
                }
            }
        }
        parent::createTransportFile($helpers, $widgets, '', 'modDashboardWidget', $mode);
        return;
        $widgetFields = array();
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_widgets'));
        if ($mode == MODE_BOOTSTRAP) {
            $widgets = $helpers->modx->getOption('widgets', ObjectAdapter::$myObjects, array());
            foreach($widgets as $widget => $fields) {
                unset($fields['dashboards']);
            }
        } elseif ($mode == MODE_EXPORT) {
            $namespaces = $helpers->props['namespaces'];
            foreach($namespaces as $namespace => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $namespace;
                $name = strtolower($name);
                $widgets = $helpers->modx->getCollection($prefix . 'modDashboardWidget', array('namespace' => $name));
            }
        }
        if (! empty($widgets)) {
            $transportFile = 'transport.widgets.php';
            $tpl = $helpers->getTpl('transportfile.php');
            $variableName = 'widgets';
            $tpl = str_replace('[[+elementType]]', $variableName, $tpl);
            $tpl = $helpers->replaceTags($tpl);
            $tpl .= '/' . '*' . ' @var xPDOObject[] ' . '$' . $variableName . ' *' . "/\n\n";
            $i = 0;

            foreach($widgets as $widget => $fields) {
                /** @var $widget modDashboardWidget */
                $widgetFields = $fields;
                $code = '';
                /*$actionFields[$i]['id'] = $i + 1;
                $code .= "\$action = \$modx->newObject(" . $prefix . "'modAction');\n";
                $code .= "\$action->fromArray( ";
                $code .= var_export($actionFields[$i], true);
                $code  .= ", '', true, true);\n";*/

                /* do Widget  */
                $widgetFields['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "widgets[";
                $code .= $i+1 . '] = ' . "\$modx->newObject(" . $prefix .  "'modDashboardWidget');\n";
                $code .= "\$";
                $code .= "widgets[";
                $code .= $i + 1 . ']->fromArray( ';
                $code .= var_export($widgetFields, true);
                $code .= ", '', false, true);\n";
 /*               $code .= "\$";
                $code .= "widgets[";
                $code .= $i + 1 . ']->addOne(';
                $code .= "\$action);\n";*/
                $tpl .= $code;
                $i++;
            }
            $tpl .= "\nreturn \$widgets;\n";
            $dryRun = $mode == MODE_EXPORT && !empty($helpers->props['dryRun']);
            $path = $helpers->props['targetRoot'] . '_build/data/';
            if (!file_exists($path . $transportFile) || $mode != MODE_BOOTSTRAP) {
                $helpers->writeFile($path, $transportFile, $tpl, $dryRun);
            } else {
                $helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                    $helpers->modx->lexicon('mc_file_already_exists')
                    . ': ' . $transportFile);
            }
        }

    }

    public static function createResolver($dir, $intersects, $helpers, $mode = MODE_BOOTSTRAP) {

        /* Create widget.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $helpers->modx->lexicon('mc_creating_widget_resolver'));
            $tpl = $helpers->getTpl('widgetresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR,
                    '[Widget Adapter] ' .
                    $helpers->modx->lexicon('mc_widgetresolvertpl_empty'));
                return false;
            }

            $fileName = 'widget.resolver.php';

            if (!file_exists($dir . '/' . $fileName) || $mode == MODE_EXPORT) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);

                $helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                    $helpers->modx->lexicon('mc_already_exists'));
            }
        }
        return true;
    }

    public function remove() {
        $fields = $this->myFields;
        /* @var $action modAction */
        /* @var $widget modDashboardWidget */
        $widget = $this->modx->getObject($this->classPrefix . 'modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
        if ($widget) {
            /* Remove widget placements here if necessary  */
            /*$placements = $widget->getMany('Placements');
            if (!empty $placements) {
                foreach($placements as $placement) {
                    if ($placement->remove()) {
                        $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                            $this->modx->lexicon('mc_removed_widget_placement')
                            . ': ' . $fields['name']);
                        $this->modx->setLogLevel($temp);
                    }
                }
            }*/
            if ($widget->remove()) {
                $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_removed_widget')
                    . ': '. $fields['name']);
                $this->modx->setLogLevel($temp);
            }
        }
    }
}
