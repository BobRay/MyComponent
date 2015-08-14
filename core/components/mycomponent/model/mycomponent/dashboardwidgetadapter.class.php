<?php

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
        if (is_array($fields)) {
            if (!isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->getProp('packageNameLower');
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
        $obj = $this->modx->getObject('modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
        if (! $obj) {
            $widget = $this->modx->newObject('modDashboardWidget');
            $widget->fromArray($fields, '', false, true);

            if ($widget->save()) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_widget')
                    . ': ' . $fields['name']);
                $widget = $this->modx->getObject('modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
                if ($widget) {
                    $id = $widget->get('id');
                    $widgetPlacement = $this->modx->getObject('modDashboardWidgetPlacement', array('dashboard'=> 1, 'widget' => $id));
                    if (! $widgetPlacement) {
                        $widgetPlacement = $this->modx->newObject('modDashboardWidgetPlacement');
                        $widgetPlacement->set('dashboard', $dashboard);
                        $widgetPlacement->set('widget', $id);
                        $widgetPlacement->set('rank', $rank);
                        $widgetPlacement->save();
                    }

                }
            }
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_widget_already_exists')
                    . ' ' . $fields['text']);
        }
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $widgetFields = array();
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_widgets'));
        if ($mode == MODE_BOOTSTRAP) {
            $widgets = $helpers->modx->getOption('widgets', ObjectAdapter::$myObjects, array());
            foreach($widgets as $widget => $fields) {
                $rank = $fields['rank'];
                $dashboard = $fields['dashboard'];
                unset($fields['rank'], $fields['dashboard']);
                $widgetFields[] = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            /* $namespaces = $helpers->modx->getOption('namespaces',
                ObjectAdapter::$myObjects, array()); */
            $namespaces = $helpers->props['namespaces'];
            foreach($namespaces as $namespace => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $namespace;
                $name = strtolower($name);
                $widgets = $helpers->modx->getCollection('modDashboardWidget', array('namespace' => $name));
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

            foreach($widgets as $widget) {
                /** @var $widget modDashboardWidget */
                $widgetFields = $widget->toArray();
                $code = '';
                /*$actionFields[$i]['id'] = $i + 1;
                $code .= "\$action = \$modx->newObject('modAction');\n";
                $code .= "\$action->fromArray( ";
                $code .= var_export($actionFields[$i], true);
                $code  .= ", '', true, true);\n";*/

                /* do Widget  */
                $widgetFields['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "widgets[";
                $code .= $i+1 . '] = ' . "\$modx->newObject('modDashboardWidget');\n";
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

    public function remove() {
        $fields = $this->myFields;
        /* @var $action modAction */
        /* @var $widget modDashboardWidget */
        $widget = $this->modx->getObject('modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace']));
        if ($widget) {
            /* Remove widget placements here if necessary  */
            /*$placements = $widget->getMany('Placements');
            if (!empty *$placements) {
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