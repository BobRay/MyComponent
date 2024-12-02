<?php

/* $modx->lexicon->load('mycomponent:default'); */

class DashboardAdapter extends ObjectAdapter {
    protected $dbClass = 'modDashboard';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'namespace';
    protected $myFields = array();

    final public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $helpers Helpers */

        parent::__construct($modx, $helpers);

        $this->name = $fields['name'];

        if (is_array($fields)) {
            ObjectAdapter::$myObjects['dashboards'][] = $fields;
            $this->myFields = $fields;
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[Dashboard Adapter] ' .
                $this->modx->lexicon('mc_no_fields'));
        }
    }

    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        if ($this->isMODX3) {
            $fields['customizable'] = $this->modx->getOption('customizable', $fields, '1', true);
        } else {
            unset($fields['customizable']);
        }

        $fields['hide_trees'] = $this->modx->getOption('hide_trees', $fields, '0', true);

        $obj = $this->modx->getObject($this->classPrefix . 'modDashboard', array('name' => $fields['name']));
        if (! $obj) {
            $dashboard = $this->modx->newObject($this->classPrefix . 'modDashboard');
            $dashboard->fromArray($fields, '', false, true);

            if ($dashboard->save()) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_dashboard')
                    . ': ' . $fields['name']);
            }
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_dashboard_already_exists')
                    . ' ' . $fields['name']);
        }
    }

    /******* Dead Code *******/
    public static function XcreateTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $isMODX3 = $helpers->modx->getVersionData()['version'] >= 3;
        $prefix = $isMODX3
            ? 'MODX\Revolution\\'
            : '';

        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_dashboards'));

        $dashboards = $helpers->modx->getOption('dashboards', ObjectAdapter::$myObjects, array());

        if ($mode == MODE_EXPORT) {
            foreach ($dashboards as $key => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $key;
                $object = $helpers->modx->getObject( $prefix . 'modDashboard', array('name' => $name));
                $fields = $object->toArray();
            }

        }
        parent::createTransportFile($helpers, $dashboards, '', 'modDashboard', $mode);
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
        if (! empty($dashboards)) {
            $transportFile = 'transport.dashboards.php';
            $tpl = $helpers->getTpl('transportfile.php');
            $variableName = 'dashboards';
            $tpl = str_replace('[[+elementType]]', $variableName, $tpl);
            $tpl = $helpers->replaceTags($tpl);
            $tpl .= '/' . '*' . ' @var xPDOObject[] ' . '$' . $variableName . ' *' . "/\n\n";
            $i = 0;

                foreach($dashboards as $dashboard => $fields) {
                /** @var $widget modDashboard */
                $dashboardFields = $fields;

                /* do Widget  */
                $dashboardsFields['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "dashboards[";
                $code .= $i+1 . '] = ' . "\$modx->newObject(" . $prefix .  "'modDashboard');\n";
                $code .= "\$";
                $code .= "dashboard[";
                $code .= $i + 1 . ']->fromArray( ';
                $code .= var_export($dashboardFields, true);
                $code .= ", '', false, true);\n";
                $i++;
            }
            $tpl .= "\nreturn \$dashboards;\n";
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

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $dashboardFields = array();
        $prefix = $helpers->modx->getVersionData()['version'] >= 3
            ? 'MODX\Revolution\\'
            : '';

        $dashboards = $helpers->modx->getOption('dashboards', ObjectAdapter::$myObjects, array());

        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_dashboards'));
        if ($mode == MODE_BOOTSTRAP) {
            foreach ($dashboards as $dashboard => $fields) {
                $dashboardFields[] = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            /* Get fields from actual objects */
            foreach ($dashboards as $key => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $key;
                $dashboardObject = $helpers->modx->getObject($prefix . 'modDashboard', array('name' => $name));

                if ($dashboardObject) {
                    $dashboardFields[$key] = $dashboardObject->toArray();
                    unset($dashboardFields[$key]['id']);
                }
            }
        }

        if (!empty($dashboardFields)) {
            $transportFile = 'transport.dashboards.php';
            $tpl = $helpers->getTpl('transportfile.php');
            $variableName = 'dashboards';
            $tpl = str_replace('[[+elementType]]', $variableName, $tpl);
            $tpl = $helpers->replaceTags($tpl);
            $tpl .= '/' . '*' . ' @var xPDOObject[] ' . '$' . $variableName . ' *' . "/\n\n";
            $i = 0;
            foreach ($dashboardFields as $k => $fields) {
                $code = '';

                /* do dashboard item */
                // $dashboardFields[$i]['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "dashboards[";
                $code .= $i + 1 . '] = ' . "\$modx->newObject(" . $prefix . "'modDashboard');\n";
                $code .= "\$";
                $code .= "dashboards[";
                $code .= $i + 1 . ']->fromArray( ';
                $code .= var_export($dashboardFields[$i], true);
                $code .= ", '', true, true);\n";

                $tpl .= $code;
                $i++;
            }
            $tpl .= "\nreturn \$dashboards;\n";

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
        /* @var $dashboard modDashboard */
        $dashboard = $this->modx->getObject($this->classPrefix . 'modDashboard', array('name' => $fields['name']));
        if ($dashboard->remove()) {
            $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                $this->modx->lexicon('mc_removed_dashboard')
                . ': '. $fields['name']);
            $this->modx->setLogLevel($temp);
        }
    }
}
