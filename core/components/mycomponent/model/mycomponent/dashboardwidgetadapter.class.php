<?php

/* $modx->lexicon->load('mycomponent:default'); */

class DashboardWidgetAdapter extends ObjectAdapter {
    protected $dbClass = 'modDashboardWidget';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'namespace';
    protected $myFields;
    protected $mode;

    final public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $helpers Helpers */

        parent::__construct($modx, $helpers);

        $this->name = $fields['name'];
        $this->mode = $mode;

        if (is_array($fields)) {
            if (!isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->getProp('packageNameLower');
            }
            if (isset($fields['placements'])) {
                $this->setWidgetResolver($fields['placements'], $mode);
                unset($fields['dashboards']);
            }

            ObjectAdapter::$myObjects['widgets'][] = $fields;
            $this->myFields = $fields;
        }
    }

    /* Adds one widget to MODX */
    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        $widgetId = null;
        $placements = $fields['placements'];
        unset($fields['placements']);

        $obj = $this->modx->getObject($this->classPrefix . 'modDashboardWidget', array('name' => $fields['name'], 'namespace' => $fields['namespace'], 'type' => $fields['type']));
        if (!$obj) {
            /** @var modDashboardWidget $widget */
            $widget = $this->modx->newObject($this->classPrefix . 'modDashboardWidget');
            $widget->fromArray($fields, '', false, true);

            if ($widget->save()) {
                $widgetId = $widget->get('id');

                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_widget')
                    . ': ' . $fields['name']);
            }
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_widget_already_exists')
                . ' ' . $fields['name']);
        }

        if ($fields['type'] === 'file') {
            /* Other types handled elsewhere */
            $filePath = $fields['content'];
            $parsedFilePath = $this->helpers->getWidgetFilePath($filePath);
            $dir = $parsedFilePath['dir'];
            if (! is_dir($dir)) {
                mkdir($dir, $this->helpers->props['dirPermission'], true);
            }
            $fileName = $parsedFilePath['filename'];
            $fullPath = $dir . $fileName;
            if (! file_exists($fullPath)) {
                $tpl = $this->modx->getChunk('phpfile.php');
                if ($tpl) {
                    $tpl = $this->helpers->replaceTags($tpl);
                    file_put_contents($fullPath, $tpl);
                }
            } else {
                $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_file_already_exists')
                    . ' ' . $fullPath);
            }
        }
    }

    public function setWidgetResolver($placements, $mode)
    {
        if (($mode == MODE_BOOTSTRAP) || ($mode == MODE_EXPORT)) {
            foreach ($placements as $placement) {
                $rank = isset($placement['rank'])
                    ? $placement['rank']
                    : '0';

                $size = isset($placement['size'])
                    ? $placement['size']
                    : 'half';

                $resolverFields = array();
                $resolverFields['dashboard'] = $placement['dashboard'];
                $resolverFields['widget'] = $this->getName();

                $resolverFields['rank'] = $rank;
                $resolverFields['size'] = $size;

                ObjectAdapter::$myObjects['widgetResolver'][] = $resolverFields;
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
