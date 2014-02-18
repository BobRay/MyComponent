<?php

abstract class ElementAdapter extends ObjectAdapter {

    /**
     * @var
     */
    protected $categoryId;
    // Database fields for the XPDO Object
    /**
     * @var $myFields array - Object's fields
     */
    protected $myFields;

    /** @var $modx modX - $modx object */
    public $modx;

    /** @var $helpers Helpers - $helpers object */
    public $helpers;

    /**
     * @var $categoryName string - Category Name
     */
    public $categoryName;

    /**
     * @var $content string - contents of object's content field
     */
    public $content = null;


    /**
     * Class constructor
     *
     * @param modX $modx - $modx object referrence
     * @param Helpers $helpers - $helpers object
     * @param $fields array - Object fields
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT
     */
    public function __construct(&$modx, $helpers, $fields, $mode = MODE_BOOTSTRAP) {

        $this->modx =& $modx;
        $this->helpers =& $helpers;

        if (!isset($fields['id'])) {
            $fields['id'] = '';
        }

        parent::__construct($modx, $helpers);
        if ($this->dbClass != 'modPropertySet') {
            $this->setPropertySetResolver($fields, $mode);
        }
        if (isset($fields['propertySets'])) {
            unset($fields['propertySets']);
        }
        if ($mode == MODE_BOOTSTRAP) {
            if (is_array($fields)) {
                $this->categoryName = $fields['category'];
                $this->fieldsToIds($fields);
                $this->myFields = $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            if ($this->dbClass !== 'modPropertySet')  {
                $this->content = $fields['content'];
                unset($fields['content']);
            }
            $this->fieldsToNames($fields);
            $this->categoryName = $fields['category'];
            $this->myFields = $fields;
        }
        ObjectAdapter::$myObjects['ElementCategories'][$this->categoryName]['elements'][$this->dbClass][] = $fields;
    }

    /**
     * @param $fields array - Object fields
     * @param $mode int - MODE_BOOTSTRAP, MODE_EXPORT
     */
    public function setPropertySetResolver($fields, $mode) {
        if ($mode == MODE_BOOTSTRAP) {
            if (! isset($fields['propertySets'])) {
                return;
            } else {
                $sets = $fields['propertySets'];
            }
            foreach ($sets as $k => $setName) {
                $resolverFields = array(
                    'element' => isset($fields['element'])
                        ? $fields['element']
                        : $this->getName(),
                    'element_class' => isset($fields['element_class'])
                        ? $fields['element_class']
                        : $this->dbClass,
                    'property_set' => isset($fields['property_set'])
                        ? $fields['property_set']
                        : $setName,
                );
                ObjectAdapter::$myObjects['propertySetResolver'][] = $resolverFields;
            }
        } elseif ($mode == MODE_EXPORT) {
            /* Don't export property sets unless set in config */
            if (!isset($fields['propertySets'])) {
                return;
            }
            $alias = $this->helpers->getNameAlias($this->dbClass);
            $me = $this->modx->getObject($this->dbClass, array($alias => $this->getName()));
            /* @var $me modElement */
            if (!$me) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, "[ElementAdapter] " .
                    $this->modx->lexicon('mc_self_nf'));
            } else {
                $eps = $me->getMany('PropertySets');
                if (!empty($eps)) {
                   foreach ($eps as $ep) {
                       /* @var $ep modElementPropertySet */
                       $fields = $ep->toArray();
                       /* @var $propertySetObj modPropertySet */
                       $propertySetObj = $this->modx->getObject('modPropertySet',
                           $fields['property_set']);
                       $propertySetName = $propertySetObj->get('name');
                       $resolverFields = array(
                           'element' => $this->getName(),
                           'property_set' => $propertySetName,
                           'element_class' => $this->dbClass,
                       );
                       ObjectAdapter::$myObjects['propertySetResolver'][] = $resolverFields;
                   }
                }
            }
        }
    }



    /**
     * Converts object fields containing IDs to the names of the objects
     * represented by the IDs -- only executes on export.
     * @param $fields array
     */
    public function fieldsToNames(&$fields) {
        /* @var $categoryObj modCategory */
        $categoryObj = $this->modx->getObject('modCategory', $fields['category']);
        if ($categoryObj) {
            $fields['category'] = $categoryObj->get('category');
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[Element Adapter] ' .
                $this->modx->lexicon('mc_category_nf')
                . ': ' . $fields['category']);
        }
    }

    /**
     * Converts object fields containing names to the IDs of the objects
     * represented by the names.
     * @param $fields array
     */

    public function fieldsToIds(&$fields){
        if (isset($fields['category'])) {
            $categoryObj = $this->modx->getObject('modCategory', array('category' => $fields['category']));
            if ($categoryObj) {
                $fields['category'] = $categoryObj->get('id');
            }
        }
    }

    /**
     * Set thing up for ObjectAdapter to create transport files
     * @param $helpers
     * @param int $mode
     */
    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */

        $categories = $helpers->modx->getOption('ElementCategories', ObjectAdapter::$myObjects, array());
        if (empty($categories)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                $helpers->modx->lexicon('mc_no_elements_to_process'));
            return;
        }

        foreach($categories as $category => $elementList) {
            $category = strtolower($category);
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $helpers->modx->lexicon('mc_processing_transport_files_for_category')
            . ': ' . $category);
            foreach($elementList['elements'] as $type => $elements) {
                $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
                    $helpers->modx->lexicon('mc_processing')
                     . ' ' . $type);

                foreach($elements as $k => $fields ) {
                    $alias = $helpers->getNameAlias($type);
                    $helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                        $helpers->modx->lexicon('mc_processing_object')
                     . ': ' . $fields[$alias]);

                }
                parent::createTransportFile($helpers, $elements, $category, $type, $mode);
            }
        }

    }


    /**
     * Handle static elements before ObjectAdapter
     * adds to MODX
     *
     * @param bool $overwrite
     * @return bool|int
     */
    public function addToMODx($overwrite = false) {
        unset($this->myFields['propertySets']);
        $fields = $this->myFields;

        $class = $this->getClass();
        $allStatic = $this->modx->getOption('allStatic', $this->helpers->props, false);
        if (($class != 'modPropertySet') && ($class != 'modPlugin') && $allStatic) {
            $fields['static'] = true;
        }

        /* Handle static elements */
        if (isset($fields['static']) && !empty($fields['static'])) {
            $projectDir = str_replace(MODX_ASSETS_PATH . 'mycomponents/',
                '',$this->helpers->props['targetRoot']);
            $dir = 'assets/mycomponents/';
            $dir .= $projectDir . 'core/components/' .
                $this->helpers->props['packageNameLower'] . '/';
            $path = $this->helpers->getCodeDir($dir, $this->dbClass);
            if (isset($this->myFields['filename'])) {
                $fileName = $this->myFields['filename'];
            } else {
                $fileName = $this->helpers->getFileName($this->getName(), $this->dbClass);
            }
            $path .= '/' . $fileName;
            $this->myFields['source'] = $this->modx->getOption('default_media_source');
            $this->myFields['static_file'] = $path;
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $this->modx->lexicon('mc_set_static_path_to')
                . ' ' . $path);
        }
        return parent::addToMODx($overwrite);
    }
}