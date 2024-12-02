<?php

class MenuAdapter extends ObjectAdapter {
    protected $dbClass = 'modMenu';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'text';
    protected $dbClassParentKey = 'parent';
    protected $myFields;

    final public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $helpers Helpers */

        parent::__construct($modx, $helpers);
        if (is_array($fields)) {
            if (!isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->getProp('packageNameLower');
            }
            ObjectAdapter::$myObjects['menus'][] = $fields;
            $this->myFields = $fields;
        }
    }

    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        $obj = $this->modx->getObject($this->classPrefix . 'modMenu', array('text' => $fields['text'], 'parent' => $fields['parent']));
        if (! $obj) {
            unset($fields['id']);
            $menu = $this->modx->newObject($this->classPrefix . 'modMenu');
            $menu->fromArray($fields, '', true, true);
            // $menu->addOne($action);
            if ($menu->save()) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_menu')
                    . ': ' . $fields['text']);
            }
        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_menu_already_exists')
                    . ' ' . $fields['text']);
        }
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $menuFields = array();
        $actionFields = array();
        $prefix = $helpers->modx->getVersionData()['version'] >= 3
            ? 'MODX\Revolution\\'
            : '';

        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_menus'));
        if ($mode == MODE_BOOTSTRAP) {
            $menus = $helpers->modx->getOption('menus', ObjectAdapter::$myObjects, array());
            foreach($menus as $menu => $fields) {
                /*$actionFields[] = $fields['action'];
                unset($fields['action']);*/
                $menuFields[] = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            $nameSpaces = $helpers->modx->getOption('nameSpaces',
                ObjectAdapter::$myObjects, array());
            foreach($nameSpaces as $namespace => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $namespace;
                $name = strtolower($name);
                $menus = $helpers->modx->getCollection($prefix . 'modMenu', array('namespace' => $name));
                foreach($menus as $menu) {
                    /* @var $menu modMenu */
                        $m_fields = $menu->toArray();
                        if (!isset($m_fields['namespace'])) {
                            $m_fields['namespace'] = !empty($namespace) ? $namespace
                                : $helpers->getProp('packageNameLower');
                        }

                        $menuFields[] = $m_fields;
                }
            }
        }
        if (! empty($menuFields)) {
            $transportFile = 'transport.menus.php';
            $tpl = $helpers->getTpl('transportfile.php');
            $variableName = 'menus';
            $tpl = str_replace('[[+elementType]]', $variableName, $tpl);
            $tpl = $helpers->replaceTags($tpl);
            $tpl .= '/' . '*' . ' @var xPDOObject[] ' . '$' . $variableName . ' *' . "/\n\n";
            $i = 0;
            foreach($menuFields as $k => $fields) {
                $code = '';

                /* do Menu item */
                $menuFields[$i]['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "menus[";
                $code .= $i+1 . '] = ' . "\$modx->newObject(" . $prefix . "'modMenu');\n";
                $code .= "\$";
                $code .= "menus[";
                $code .= $i + 1 . ']->fromArray( ';
                $code .= var_export($menuFields[$i], true);
                $code .= ", '', true, true);\n";

                $tpl .= $code;
                $i++;
            }
            $tpl .= "\nreturn \$menus;\n";
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
        /* @var $menu modMenu */
        $menu = $this->modx->getObject($this->classPrefix . 'modMenu', array('text' => $fields['text'], 'parent' => $fields['parent']));
        if ($menu) {
            if ($menu->remove()) {
                $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_removed_menu item')
                    . ': '. $fields['text']);
                $this->modx->setLogLevel($temp);
            }
        }
    }
}
