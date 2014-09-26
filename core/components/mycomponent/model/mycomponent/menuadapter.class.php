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
            $this->myFields = $fields;
            $actionFields = $modx->getOption('action', $fields, array());

            if (empty($actionFields)) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_menu_has_no_action'));
                return;
            }
            if (empty($actionFields['namespace'])) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_menu_action_no_namespace'));
                return;
            }
            ObjectAdapter::$myObjects['menus'][] = $fields;
            $this->myFields = $fields;

        }




    }

    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        $obj = $this->modx->getObject('modMenu', array('text' => $fields['text'], 'parent' => $fields['parent']));
        if (! $obj) {
            $actionFields = $fields['action'];
            $action = $this->modx->newObject('modAction');
            $action->fromArray($actionFields, '', true, true);
            unset($fields['action'], $fields['id']);
            $menu = $this->modx->newObject('modMenu');
            $menu->fromArray($fields, '', true, true);
            $menu->addOne($action);
            if ($menu->save()) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_created_menu')
                    . ': ' . $fields['text']);
            /* Refresh the action map */
            $cm = $this->modx->getCacheManager();
            $cm->refresh(array(
                'action_map' => array(),
            ));
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
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_menus'));
        if ($mode == MODE_BOOTSTRAP) {
            $menus = $helpers->modx->getOption('menus', ObjectAdapter::$myObjects, array());
            foreach($menus as $menu => $fields) {
                $actionFields[] = $fields['action'];
                unset($fields['action']);
                $menuFields[] = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            $nameSpaces = $helpers->modx->getOption('nameSpaces',
                ObjectAdapter::$myObjects, array());
            foreach($nameSpaces as $namespace => $fields) {
                $name = isset($fields['name']) ? $fields['name'] : $namespace;
                $name = strtolower($name);
                $actions = $helpers->modx->getCollection('modAction', array('namespace' => $name));
                foreach($actions as $action) {
                    /* @var $action modAction */
                    /* @var $menu modMenu */
                    $menus = $action->getMany('Menus');
                    foreach($menus as $menu) {
                        $m_fields = $menu->toArray();
                        if (!isset($m_fields['namespace'])) {
                            $m_fields['namespace'] = !empty($namespace) ? $namespace
                                : $helpers->getProp('packageNameLower');
                        }
                        unset($m_fields['action']);
                        $menuFields[] = $m_fields;
                        $actionObj = $menu->getOne('Action');
                        $a_fields = $actionObj->toArray();
                        unset($a_fields['id']);
                        $actionFields[] = $a_fields;
                    }

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
                $actionFields[$i]['id'] = $i + 1;
                /* do Action */
                $code .= "\$action = \$modx->newObject('modAction');\n";
                $code .= "\$action->fromArray( ";
                $code .= var_export($actionFields[$i], true);
                $code  .= ", '', true, true);\n";

                /* do Menu item */
                $menuFields[$i]['id'] = $i + 1;
                $code .= "\n";
                $code .= "\$";
                $code .= "menus[";
                $code .= $i+1 . '] = ' . "\$modx->newObject('modMenu');\n";
                $code .= "\$";
                $code .= "menus[";
                $code .= $i + 1 . ']->fromArray( ';
                $code .= var_export($menuFields[$i], true);
                $code .= ", '', true, true);\n";
                $code .= "\$";
                $code .= "menus[";
                $code .= $i + 1 . ']->addOne(';
                $code .= "\$action);\n";


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
        $menu = $this->modx->getObject('modMenu', array('text' => $fields['text'], 'parent' => $fields['parent']));
        if ($menu) {
            $action = $menu->getOne('Action');
            if ($action) {
                if ($action->remove()) {
                    $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                        $this->modx->lexicon('mc_removed_menu_item')
                        . ': ' . $fields['text']);
                    $this->modx->setLogLevel($temp);
                }
            }
            if ($menu->remove()) {
                $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_removed_action')
                    . ': '. $fields['text']);
                $this->modx->setLogLevel($temp);
            }
        }
    }


}