<?php
// Include the Base Class (only once)
require_once('objectadapter.class.php');

class ResourceAdapter extends ObjectAdapter {
//These will never change.

    /**
     * @var $dbClass string - database class name
     */
    protected $dbClass = 'modResource';

    /**
     * @var $dbClassIDKey string - name of primary key field
     */
    protected $dbClassIDKey = 'id';

    /**
     * @var $dbClassNameKey string - name of "name" field
     */
    protected $dbClassNameKey = 'pagetitle';

    /**
     * @var $dbClassParentKey string - name of parent field
     */
    protected $dbClassParentKey = 'parent';

    /**
     * @var $createProcessor string - create processor path
     */
    protected $createProcessor = 'resource/create';

    /**
     * @var $updateProcessor string - update processor path
     */
    protected $updateProcessor = 'resource/update';

    /**
     * @var $defaults array - array of default settings
     */
    protected $defaults = array();

    /**
     * @var $name string - object name
     */
    protected $name;

    /** @var $helpers Helpers */
    public $helpers;

    /** @var $modx modX */
    public $modx;

    /**
     * @var $myFields array - object fields
     */
    protected $myFields;


    /**
     * Class constructor
     *
     * @param modX $modx - $modx object referrence
     * @param Helpers $helpers - $helpers object
     * @param $fields array - Object fields
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT
     */

    function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $object modResource */
        parent::__construct($modx, $helpers);
        $this->name = $fields['pagetitle'];
        if (! isset($fields['id'])) {
            $fields['id'] = '';
        }
        if ($mode == MODE_BOOTSTRAP) {
    // Set defaults if they are not already set
            $this->defaults['published'] = $modx->getOption('publish_default', null);
            $this->defaults['richtext'] = $modx->getOption('richtext_default',null);
            $this->defaults['hidemenu'] = $modx->getOption('hidemenu_default', null);
            $this->defaults['cacheable'] = $modx->getOption('cache_default', null);
            $this->defaults['searchable'] = $modx->getOption('search_default', null);
            $this->defaults['context_key'] = $modx->getOption('default_context', null);

            if (!isset($fields['class_key'])) {
                $fields['class_key'] = 'modDocument';
            }
            foreach ($this->defaults as $field => $value) {
                $fields[$field] = isset($fields[$field])
                    ? $fields[$field]
                    : $value;
            }

        } elseif ($mode == MODE_EXPORT) {
                $this->fieldsToNames($fields);
                $this->myFields = $fields;
        }
        $this->setResourceResolver($fields, $mode);
        $this->myFields = $fields;
        ObjectAdapter::$myObjects['resources'][] = $fields;
    }

    /**
     * Prepare object for ObjectAdapter to write Resource resolver
     *
     * @param $fields
     * @param $mode
     */
    public function setResourceResolver($fields, $mode) {
        $resolverFields = array();
        $resolverFields['pagetitle'] = $fields['pagetitle'];
        $resolverFields['parent'] = isset($fields['parent'])
            ? $fields['parent']
            : '0';
        if ($resolverFields['parent'] == 'default') {
            $resolverFields['parent'] = '0';
        }
        $resolverFields['template'] = isset($fields['template'])
            ? $fields['template']
            : 'default';
        if ($mode == MODE_BOOTSTRAP && isset($fields['tvValues'])) {
            $resolverFields['tvValues'] = $fields['tvValues'];
        } elseif ($mode == MODE_EXPORT) {
            $me = $this->modx->getObject('modResource', array('pagetitle' => $fields['pagetitle']));
            if (!$me) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[ResourceAdapter] ' .
                    $this->modx->lexicon('mc_self_nf'));
            } else {
                /* Check for TVs (this is ugly, but we only want OUR TVs) */
                $myId = $me->get('id');
                /* get our categories */
                $categories = $this->modx->getOption('categories',
                    ObjectAdapter::$myObjects, array(0));
                /* get Tvs in all our categories */
                $tvObjects = array();
                foreach($categories as $categoryName => $fields ) {
                    $categoryObj = $this->modx->getObject('modCategory',
                        array('category' => $categoryName));
                    if ($categoryObj) {
                        $categoryId = $categoryObj->get('id');
                        $tvObjects = array_merge($tvObjects, $this->modx->getCollection('modTemplateVar', array('category' => $categoryId)));
                    }
                }
                /* get the TvValues */
                /* @var $tvObj modTemplateVar */
                if (!empty($tvObjects)) {
                    foreach ($tvObjects as $tvObj) {
                        $val = $tvObj->getValue($myId);
                        if (!empty($val) && $val != $tvObj->get('default_text')) {
                            $resolverFields['tvValues'][$tvObj->get('name')] = $val;
                        }
                    }
                }
            }
        }
        ObjectAdapter::$myObjects['resourceResolver'][] = $resolverFields;

    }
    /**
     * Converts object fields containing IDs to the names of the objects
     * represented by the IDs -- only executes on export.
     * @param $fields array
     * @param $mode int
     */
    public function fieldsToNames(&$fields, $mode = MODE_BOOTSTRAP) {
        if (!empty($fields['parent'])) {
            $parentObj = $this->modx->getObject('modResource', $fields['parent']);
            if ($parentObj) {
                $fields['parent'] =  $parentObj->get('pagetitle');
            } else {
                if ($mode != MODE_REMOVE) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                        '[ResourceAdapter] ' .
                            $this->modx->lexicon('mc_parent_nf')
                                . ': ' .  $fields['parent']);
                }
            }
        }
        if (!empty($fields['template'])) {
            if ($fields['template'] == $this->modx->getOption('default_template')) {
                $fields['template'] = 'default';
            } else {
                $templateObj = $this->modx->getObject('modTemplate', $fields['template']);
                if ($templateObj) {
                    $fields['template'] = $templateObj->get('templatename');
                }
            }
        }
    }

    /**
     * Converts object fields containing names to the IDs of the objects
     * represented by the names.
     * @param $fields array
     */

    public function fieldsToIds(&$fields) {
        if (!isset($fields['parent']) || $fields['parent'] == 'default') {
            $fields['parent'] = '0';
        } else {
            $parentObj = $this->modx->getObject('modResource', array('pagetitle' => $fields['parent']));
            if ($parentObj) {
                $fields['parent'] = $parentObj->get('id');
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[ResourceAdapter] ' .
                    $this->modx->lexicon('mc_parent_nf')
                        . ': ' . $fields['pagetitle']);
            }
        }
        if (!isset($fields['template']) || empty($fields['template']) || $fields['template'] == 'default') {
            $fields['template'] = $this->modx->getOption('default_template');
        } else {
            $templateObj = $this->modx->getObject('modTemplate', array('templatename' => $fields['template']));
            if ($templateObj) {
                $fields['template'] = $templateObj->get('id');
            }
        }
    }

    /**
     * Create alias if empty before ObjectAdapter adds to MODX
     * @param bool $overwrite
     * @return bool|int
     */
    final public function addToMODx($overwrite = false) {

        /* @var $modx modX */
        $fields =& $this->myFields;

        $this->fieldsToIds($fields);

        if (!isset($fields['alias']) || empty($fields['alias'])) {
            $fields['alias'] = str_replace(' ', '-', strtolower($fields['pagetitle']));
        }
        $this->myFields = &$fields;
        return parent::addToMODx($overwrite);
    }

    /**
     * Create resource resolver from intersect objects
     *
     * @param $dir string - path to resolver dir
     * @param $intersects array - array of intersect objects
     * @param $helpers Helpers - $helpers object
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT
     * @return bool - success or failure
     */
    public static function createResolver($dir, $intersects, $helpers, $mode = MODE_BOOTSTRAP) {

        /* Create resource.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $helpers->modx->lexicon('mc_creating_resource_resolver'));
            $tpl = $helpers->getTpl('resourceresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR, '[Resource Adapter] ' .
                    $helpers->modx->lexicon('mc_resourceresolvertpl_empty'));
                return false;
            }

            $fileName = 'resource.resolver.php';

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

    /**
     * Get array of resources for ObjectAdapter to use in creating
     * transport file
     *
     * @param $helpers
     * @param int $mode
     */
    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_resources'));
        $resources = $helpers->modx->getOption('resources', ObjectAdapter::$myObjects, array());
        parent::createTransportFile($helpers, $resources, '', 'modResource', $mode);
    }


    /**
     * Exports Resources from MODX to build files
     * Removes objects if $mode == MODE_REMOVE
     *
     * @param $modx modX - $modx->object
     * @param $helpers Helpers - $helpers object
     * @param $props array - $scriptProperties array
     * @param int $mode - MODX_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    static function exportResources(&$modx, &$helpers, $props, $mode = MODE_EXPORT) {
        /* @var $modx modX */
        /* @var $helpers Helpers */
        $objects = array();

        /* Add resources from exportResources array in the project config file
          to $this->myObjects array */
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $helpers->modx->lexicon('mc_processing_resources'));
        $byId = $modx->getOption('getResourcesById', $props, false);
        $method = $byId? 'ID' : 'pagetitle';
        $resources = $modx->getOption('exportResources', $props, array());
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                if ($byId) {
                    $resObject = $modx->getObject('modResource', $resource);
                } else {
                    $resObject = $modx->getObject('modResource', array('pagetitle' => trim($resource)));
                }
                if ($resObject) {
                    $objects[] = $resObject;
                } else {
                    $helpers->sendLog(modX::LOG_LEVEL_ERROR,
                        '[Resource Adapter] ' .
                        $helpers->modx->lexicon('mc_could_not_get_resource_with_method')
                        . ' ' . $method . ': ' . $resource);
                }
            }
        }
        /* if $parents is set in project config, add children (and optionally parent
           to  $resources array */
        $parents = $modx->getOption('parents', $props, array() );
        $includeParents = $modx->getOption('includeParents', $props, false);
        if (!empty($parents)) {
            foreach ($parents as $parentResource) {
                if ($byId) {
                    $parentObj = $modx->getObject('modResource', $parentResource);
                } else {
                    $parentObj = $modx->getObject('modResource', array('pagetitle' => $parentResource));
                }
                if ($parentObj) {
                    if ($includeParents) {
                        $objects[] = $parentObj;
                    }
                    $children = $parentObj->getMany('Children');
                    if (!empty ($children)) {
                        $objects = array_merge($objects, $children);
                    }
                }
            }

        }
        if (!empty($objects)) {
            /* @var $object modResource */
            $dryRun = $props['dryRun'];
            foreach($objects as $object) {
                $fields = $object->toArray();
                $a = new ResourceAdapter($modx, $helpers, $fields, $mode);
                if ($mode == MODE_REMOVE) {
                    $a->remove();
                } elseif ($mode == MODE_EXPORT) {
                    $content = $object->getContent();
                    $a->createCodeFile(true, $content,  $mode, $dryRun );
                    if (isset($fields['properties']) && !empty($fields['properties'])) {
                        $a->writePropertiesFile($a->getName(), $fields['properties'], MODE_EXPORT);
                    }
                }
            }
        } else {
            $helpers->sendLog(modX::LOG_LEVEL_ERROR, '[ResourceAdapter] ' .
                $helpers->modx->lexicon('mc_no_resources_found'));
        }
    }
}