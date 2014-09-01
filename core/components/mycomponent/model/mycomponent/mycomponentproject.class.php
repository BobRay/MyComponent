<?php

if (!defined('MODE_BOOTSTRAP')) {
    define('MODE_BOOTSTRAP', 0);
    define('MODE_EXPORT', 1);
    define('MODE_IMPORT', 2);
    define('MODE_REMOVE', 3);
}


class MyComponentProject {
    /** @var $modx modX */
    public $modx;

    /**
     * @var $myPaths array - paths to the working directories
     */
    public $myPaths = array();

    /**
     * @var $packageNameLower string - Lowercase name of the package
     */
    public $packageNameLower = '';

    /**
     * @var $packageName string - Mixed-case name of the package
     */
    public $packageName = '';

    /**
     * @var $targetRoot string - Root of the project being developed
     */
    public $targetRoot = '';

    /**
     * @var $mcRoot string - Root of the MyComponent install
     */
    public $mcRoot = '';

    /**
     * @var $props array - $scriptProperties array alias
     */
    public $props = array();

    /** @var $helpers Helpers - Helper class */
    public $helpers;

    /**
     * @var $dirPermission int - Default directory permissions for new folders
     */
    public $dirPermission;

    /**
     * @var $configPath string - path to config file
     */
    protected $configPath;

    /** External: ObjectAdapter::$myObjects array - this is the master array
     *  containing all objects being processed and their fields.
     */

    // for LexiconHelper:
    // $modx->lexicon->load('mycomponent:default');



    /**
     * Convenience method for determining if MyComponent is installed.
     *
     * @return boolean - True, if MC is installed. False, if not.
     */
    public function isMCInstalled() {
        return ! empty($this->mcRoot);
    }

    /* *****************************************************************************
       Construction and Support Functions (in MODxObjectAdapter)
    ***************************************************************************** */
    /**
     * MyComponentProject constructor
     *
     * @param $modx modX
     */
    public function __construct(&$modx) {

        if (!defined('MODE_BOOTSTRAP')) {
            session_write_close();
            die("bootstrap not defined");
        }
        $this->modx =& $modx;
    }


    /**
     * @param array $scriptProperties
     * @param string $currentProject - Usually read from file, but set for unit tests
     */
    public function init($scriptProperties = array(), $currentProject = '') {
        require dirname(__FILE__) . '/mcautoload.php';
        spl_autoload_register('mc_auto_load');

        if (empty($currentProject)) {
            $currentProjectPath = $this->modx->getOption('mc.root', null,
                $this->modx->getOption('core_path') .
                'components/mycomponent/') .
                '_build/config/current.project.php';

            if (file_exists($currentProjectPath)) {
                include $currentProjectPath;
            } else {
                session_write_close();
                die('Could not find current.project.php file at: ' . $currentProjectPath);
            }
        }
        if (empty($currentProject)) {
            session_write_close();
            die('No current Project Set');
        }

        $projectConfigPath = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/') .
            '_build/config/' . strtoLower($currentProject) . '.config.php';

        if (file_exists($projectConfigPath)) {
            $properties = include $projectConfigPath;
        } else {
            session_write_close();
            die('Could not find Project Config file at: ' . $projectConfigPath);
        }

        /* Make sure that we get usable values */
        if (!is_array($properties) or empty($properties)) {
            session_write_close();
            die('Config File was not set up correctly: ' . $projectConfigPath);
        }

        /* Properties sent in method call will override those in Project Config file */
        $properties = array_merge($properties, $scriptProperties);
        $this->packageNameLower = $this->modx->getOption('packageNameLower', $properties, '');
        $this->packageName = $this->modx->getOption('packageName', $properties, '');

        $this->mcRoot = isset($properties['mycomponentRoot'])
            ? $properties['mycomponentRoot']
            : '';
        if (empty($this->mcRoot)) {
            session_write_close();
            die('mcRoot is not set in Project Config: ' . $projectConfigPath);
        }
        if (!is_dir($this->mcRoot)) {
            session_write_close();
            die('mcRoot set in project config is not a directory: ' . $projectConfigPath);
        }
        $this->mcRoot = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/');

        $this->targetRoot = $this->modx->getOption('targetRoot', $properties, '');

        if (empty($this->targetRoot)) {
            session_write_close();
            die('targetRoot is not set in project config file');
        }
        $this->props = $properties;

        // include 'helpers.class.php';
        $helpers = new Helpers($this->modx, $this->props);
        $this->helpers = $helpers;
        $this->helpers->init();
        $this->initPaths();
        $this->dirPermission = $this->helpers->getProp('dirPermission');
        $this->updateProjectsFile($projectConfigPath);
        $this->configPath = $projectConfigPath;
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            "\n" . $this->modx->lexicon('mc_project')
            . ': ' . $this->helpers->getProp('packageName'));
        ObjectAdapter::$myObjects = array();
    }


    /**
     * Update the projects.php file with the names and project config
     * paths of each project; for use by the UI
     *
     * @param $configPath string - path to project config file
     */
    public function updateProjectsFile($configPath) {
        $projectsFile = $this->mcRoot . '_build/config/projects.php';
        $header = '<' . '?' . 'php' . "\n\n\$projects = array(\n";
        $footer = ");\nreturn \$projects;\n";
        $newContent = $this->packageNameLower . "' => '" . $configPath .
            "',\n";
        if (file_exists($projectsFile)) {
            $projects = include $projectsFile;
            if (!in_array($this->packageNameLower, array_keys($projects))) {

                $content = file_get_contents($projectsFile);

                $content = str_replace($footer, "    '" . $newContent . $footer, $content);
                $fp = fopen($projectsFile, 'w');
                fwrite($fp, $content);
                fclose($fp);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_updated_projects_file'));
            }

        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_created_projects_file'));
            $content = $header . "    '" . $newContent . $footer;
            $fp = fopen($projectsFile, 'w');
            fwrite($fp, $content);
            fclose($fp);

        }
    }


    /**
     * Set up the Path variables for the Project. Called in __construct.
     */
    public function initPaths() {

        $paths = array();
        $name = $this->helpers->getProp('packageNameLower');
        // @var $ns modNameSpace

        /*  Set the Root path for this Component */
        $paths['targetRoot'] = $this->targetRoot;
        /* Basic Paths */
        $paths['targetCore'] = $paths['targetRoot'] . 'core/components/' . $name . '/';
        $paths['targetControllers'] = $paths['targetCore'] . 'controllers/';
        $paths['targetProcessors'] = $paths['targetCore'] . 'processors/';
        $paths['targetDocs'] = $paths['targetCore'] . 'docs/';
        $paths['targetElements'] = $paths['targetCore'] . 'elements/';
        $paths['targetLexicon'] = $paths['targetCore'] . 'lexicon/';
        $paths['targetModel'] = $paths['targetCore'] . 'model/' . $name . '/';

        $paths['targetAssets'] = $paths['targetRoot'] . 'assets/components/' . $name . '/';
        $paths['targetCss'] = $paths['targetAssets'] . 'css/';
        $paths['targetJs'] = $paths['targetAssets'] . 'js/';
        $paths['targetConnectors'] = $paths['targetAssets'];
        $paths['targetImages'] = $paths['targetAssets'] . 'images/';
        $paths['targetBuild'] = $paths['targetRoot'] . '_build/';
        $paths['targetData'] = $paths['targetBuild'] . 'data/';
        $paths['targetResources'] = $paths['targetData'] . 'resources/';
        $paths['targetProperties'] = $paths['targetData'] . 'properties/';
        $paths['targetResolve'] = $paths['targetBuild'] . 'resolvers/';
        $paths['targetValidate'] = $paths['targetBuild'] . 'validators/';

        /* Set myPaths class member */
        $this->myPaths = $paths;

    }

    /* *****************************************************************************
       Bootstrap and Support Functions
    ***************************************************************************** */

    /**
     * All bootstrap processes
     */
    public function bootstrap() {
        /* enable garbage collection() */
        // gc_enable();
        $mode = MODE_BOOTSTRAP;
        ObjectAdapter::$myObjects = array();
        if (!$this->isMCInstalled()) { /* Only run if MC is installed */
            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[MyComponentProject]' . $this->modx->lexicon('mc_mycomponent_not_installed_create_new'));
            return;
        }

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_action')
         . ': ' . $this->modx->lexicon('mc_bootstrap'));

        /* Create basic files (no resolvers, transport files, or code files) */
        $this->createBasics();
        /* Create all MODX objects */
        $this->createObjects($mode);

        /* Create Validators */
        $this->createValidators();

        /* Create all Resolvers */
        $this->createResolvers($mode);

        /* Create Intersects for all many-to-many relationships */
        $this->createIntersects();

        /* Create all transport files */
        $this->createTransportFiles($mode);

    }

    /**
     * Create Adapter Objects
     * Called for both Bootstrap and ExportObjects
     *
     * For MODE_BOOTSTRAP, creates objects in MODX and
     * creates code files for them
     *
     * For MODE_EXPORT, finds objects in MODX
     * and creates code files for them.
     *
     * In both cases the object fields and resolver
     * fields are written to ObjectAdapter::myObjects
     *
     * for MODE_REMOVE, object is removed from MODX
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createObjects($mode = MODE_BOOTSTRAP) {
        if ($mode != MODE_REMOVE) {
            /* create contexts */

            $this->createContexts($mode);

            /*  Create namespace */
            $this->createNamespaces($mode);

            /* create category or categories*/
            $this->createCategories($mode);


        }

        /* create system settings */
        $this->createNewSystemSettings($mode);

        $this->createContextSettings($mode);

        /* create new system events */
        $this->createNewSystemEvents($mode);

        /* Create elements */
        $this->createElements($mode);

        /* Create resources */
        $this->createResources($mode);

        /* Create menus */
        $this->createMenus($mode);

        if ($mode == MODE_REMOVE) {
            /*  Create namespace */
            $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_removing_objects'));
            $this->modx->setLogLevel($temp);
            $this->createNamespaces($mode);

            /* create category or categories*/
            $this->createCategories($mode);

            $this->createContexts($mode);
        }
    }

    /**
     * Create, export, or remove Contexts in MODX if in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createContexts($mode = MODE_BOOTSTRAP) {
        $contexts = $this->helpers->getProp('contexts', array());
        if (!empty($contexts)) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_contexts'));
            }
            foreach ($contexts as $context => $fields) {
                if (! isset($fields['key'])) {
                    $fields['key'] = $context;
                }

                if ($mode == MODE_BOOTSTRAP) {
                    // include 'contextadapter.class.php';
                    $this->addToModx('ContextAdapter', $fields);
                } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
                    $a = new ContextAdapter($this->modx, $this->helpers, $fields);
                    if ($mode == MODE_REMOVE) {
                        $a->remove();
                    }
                }
            }
        }
    }

    /**
     * Create, export, or remove namespace(s) if listed in project config
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createNamespaces($mode = MODE_BOOTSTRAP) {
        $namespaces = $this->helpers->getProp('namespaces', array());
        if (!empty($namespaces)) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_namespaces'));
            }
            foreach ($namespaces as $namespace => $fields) {
                if ($mode == MODE_BOOTSTRAP) {
                    // include 'namespaceadapter.class.php';
                    $this->addToModx('NamespaceAdapter', $fields);
                } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
                    $a = new NamespaceAdapter($this->modx, $this->helpers, $fields);
                    if ($mode == MODE_REMOVE) {
                        $a->remove();
                    }

                }

            }
        }
    }

    /**
     * Create, export, or remove categories specified in the project config file
     * If MODE_EXPORT or MODE_REMOVE, the category object exports or removes
     * its elements
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createCategories($mode = MODE_BOOTSTRAP) {
        $categories = $this->modx->getOption('categories', $this->props, array());
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_processing_categories'));
        if (empty($categories)) {
            $packageName = $this->packageName;
            if (empty($packageName)) {
                session_write_close();
                die('PackageName nor categories found in project config');
            }
            /* If no categories, create one based on packageName */
            $categories = array(
                $packageName => array(
                    'category' => $packageName,
                    'parent' => '0',
                ),
            );
        }

        foreach ($categories as $categoryName => $fields) {
            if (empty($fields['category'])) {
                $fields['category'] = $categoryName;
            }
            // include 'categoryadapter.class.php';
            $o = new CategoryAdapter($this->modx, $this->helpers, $fields, $mode);

            if ($mode == MODE_BOOTSTRAP) {
                $o->addToModx();
            } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
                /* The Category will process any elements in the category */
                /* that are specified in the project config file 'process' member */
                $elementsToProcess = $this->modx->getOption('process', $this->props, array());
                $possibleElements = array(
                    'snippets',
                    'plugins',
                    'chunks',
                    'templates',
                    'templateVars',
                    'propertySets'
                );
                $toProcess = array();
                if ($mode == MODE_REMOVE) {
                    $toProcess = $possibleElements;
                } else {
                    foreach ($possibleElements as $element) {
                        if (in_array($element, $elementsToProcess)) {
                            $toProcess[] = $element;
                        }
                    }
                }
                unset($elementsToProcess, $possibleElements);
                if ($mode == MODE_EXPORT) {
                    $dryRun = $this->helpers->getProp('dryRun');
                    $o->exportElements($toProcess, !empty($dryRun));
                } elseif ($mode = MODE_REMOVE) {
                    $o->remove($toProcess);
                }
            }
        }

        /* Update the category.php file if necessary */
        $dir = $this->targetRoot . '_build/config';
        CategoryAdapter::writeCategoryFile($dir, $this->helpers);

    }

    /**
     * Create, export, or remove any new System Settings specified in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createNewSystemSettings($mode = MODE_BOOTSTRAP) {

        $newSystemSettings = $this->modx->getOption('newSystemSettings', $this->props, array());
        if (empty($newSystemSettings) && $mode == MODE_BOOTSTRAP) {
            return;
        }
        if ($mode != MODE_EXPORT) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing_new_system_settings'));
        }
        if ($mode == MODE_BOOTSTRAP) {
            $settings = $this->helpers->getProp('newSystemSettings', array());
            foreach ($settings as $key => $fields) {
                if (!isset($fields['key'])) {
                    $fields['key'] = $key;
                }
                // include 'systemsettingadapter.class.php';
                $this->addToModx('SystemSettingAdapter', $fields);
            }

        } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
            $nameSpaces = $this->modx->getOption('namespaces', $this->props, array());
            /* Get namespaces from Project config */
            if (empty($nameSpaces)) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    '    ' .
                    $this->modx->lexicon('mc_no_namespaces'));
                return;
            }

            foreach ($nameSpaces as $nameSpace => $n_fields) {
                $name = isset($n_fields['name']) ? $n_fields['name'] : $nameSpace;
                $nameSpace = $this->modx->getObject('modNamespace',
                    array('name' => $name));
                if ($nameSpace) {
                    /* Get settings as namespace related objects */
                    $settings = $nameSpace->getMany('SystemSettings');
                    $count = count($settings);
                    if (! empty ($settings)) {
                        foreach ($settings as $setting) {
                            /* @var $setting modSystemSetting */
                            $fields = $setting->toArray();
                            /* Let Value in config file override DB value */
                            if (isset($newSystemSettings[$fields['key']]['value'])) {
                                $fields['value'] = $newSystemSettings[$fields['key']]['value'];
                            }
                            new SystemSettingAdapter($this->modx, $this->helpers, $fields, $mode);
                            if ($mode == MODE_REMOVE) {
                                $setting->remove();
                            }
                        }
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                            '    ' .
                                $this->modx->lexicon('mc_no_settings')
                                . ': ' . $name);
                    }
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                        '[MyComponentProject] ' .
                            $this->modx->lexicon('mc_namespace_nf')
                            . ': ' . $name);
                }
            }
        }
    }

    /**
     * Create, export, or remove any Context Settings specified
     * in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createContextSettings($mode = MODE_BOOTSTRAP) {
        $newContextSettings = array();
        if ($mode == MODE_BOOTSTRAP) {
            $newContextSettings = $this->modx->getOption('contextSettings', $this->props, array());
        } else {
            $namespaces = $this->modx->getOption('namespaces', $this->props, array());
            foreach($namespaces as $namespace => $fields) {
                $namespaceName = isset($fields['name'])? $fields['name'] : $namespace;
                $settings = $this->modx->getCollection('modContextSetting', array('namespace' => $namespaceName));
                $newContextSettings = array_merge($newContextSettings, $settings);
            }
        }

        if (empty($newContextSettings)) {
            return;
        }
        if ($mode != MODE_EXPORT) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing_context_settings'));
        }
        if ($mode == MODE_BOOTSTRAP) {
            $contextSettings = $this->helpers->getProp('contextSettings', array());
            foreach ($contextSettings as $key => $fields) {
                if (!isset($fields['key'])) {
                    $fields['key'] = $key;
                }
                // include 'contextsettingadapter.class.php';
                $this->addToModx('ContextSettingAdapter', $fields);
            }

        } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
            /* @var $obj modContextSetting  */

            foreach ($newContextSettings as $obj) {
                    $fields = $obj->toArray();
                    new ContextSettingAdapter($this->modx, $this->helpers, $fields, $mode);
                    if ($mode == MODE_REMOVE) {
                        $obj->remove();
                    }
            }
        }
    }


    /** Create, export, or remove any new System Events specified
     * in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createNewSystemEvents($mode = MODE_BOOTSTRAP) {
        $newSystemEvents = $this->modx->getOption('newSystemEvents', $this->props, array());
        if (empty($newSystemEvents)) {
            return;
        }
        if ($mode != MODE_EXPORT) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing_new_system_events'));
        }
        if ($mode == MODE_BOOTSTRAP) {
            foreach ($newSystemEvents as $key => $fields) {
                $fields['name'] = isset($fields['name'])
                    ? $fields['name']
                    : $key;

                // include 'systemeventadapter.class.php';
                $this->addToModx('SystemEventAdapter', $fields);
            }
        } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
            /* These come from the project config file */
            foreach ($newSystemEvents as $k => $fields) {
                $obj = $this->modx->getObject('modEvent',
                    array('name' => $fields['name']));
                if ($obj) {
                    $fields = $obj->toArray();
                    // include 'systemeventadapter.class.php';
                    $a = new SystemEventAdapter($this->modx, $this->helpers, $fields);
                    if ($mode == MODE_REMOVE) {
                        $a->remove();
                    }
                }
            }
        }

    }

    /**
     * Create, export, or remove Elements specified in
     * the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createElements($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP || $mode == MODE_REMOVE) {
            /* Create elements from the project config file.
             * In Export, they're pulled by category in the
             * CategoryAdapter, so not done here */
            if (isset($this->props['elements']) && !empty($this->props['elements'])) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_elements'));
                $elements = $this->helpers->getProp('elements', array());
                foreach ($elements as $element => $elementObjects) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n    " .
                        $this->modx->lexicon('mc_processing')
                        . ' ' . ucfirst($element));
                    foreach ($elementObjects as $elementName => $fields) {
                        /* @var $adapter elementAdapter */
                        /* @var $o ObjectAdapter */
                        $adapterName = ucFirst(substr($element, 0, -1)) . 'Adapter';
                        $fields['name'] = isset($fields['name'])
                            ? $fields['name']
                            : $elementName;
                        if ($mode == MODE_BOOTSTRAP) {
                            $o = $this->addToModx($adapterName, $fields);
                            $o->createCodeFile();
                        } elseif ($mode == MODE_REMOVE) {
                            $o = new $adapterName($this->modx, $this->helpers, $fields);
                            $o -> remove();
                        }
                    }
                }
            }
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[MyComponentProject]' .
                $this->modx->lexicon('mc_illegal_create_elements'));
        }
    }

    /**
     * Create, export, or remove Resources specified in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createResources($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP) {
            if (isset($this->props['resources']) && !empty($this->props['resources'])) {
                /* @var $o ResourceAdapter */
                $o = null;
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_resources'));
                $resources = $this->helpers->getProp('resources', array());
                foreach ($resources as $resource => $fields) {
                    $fields['pagetitle'] = empty($fields['pagetitle'])
                        ? $resource
                        : $fields['pagetitle'];

                    // include 'resourceadapter.class.php';
                    $o = $this->addToModx('ResourceAdapter', $fields);
                    $o->createCodeFile();
                }
            }
        } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
            /* Resource Adapter gets resources based on the project config file's
             * 'ExportResources' member */
            ResourceAdapter::exportResources($this->modx, $this->helpers, $this->props, $mode);
        }
    }

    /**
     * Create, export, or remove menus and actions
     * specified in the project config file
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createMenus($mode = MODE_BOOTSTRAP) {
        $menus = $this->helpers->getProp('menus', array());
        if (!empty($menus)) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_menus'));
            }
            foreach ($menus as $k => $fields) {
                if ($mode == MODE_BOOTSTRAP) {
                    // include 'menuadapter.class.php';
                    $this->addToModx('MenuAdapter', $fields);
                } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
                    $a = new MenuAdapter($this->modx, $this->helpers, $fields, $mode);
                    if ($mode == MODE_EXPORT) {
                        //$a->export();
                    } elseif ($mode == MODE_REMOVE) {
                        $a->remove();
                    }

                }

            }
        }
    }


    /**
     * Called on Bootstrap to add items to MODX
     * Separating this allows more frequent garbage collection
     * @param $adapter string - name of adapter class
     * @param $fields array - array of object fields
     * @param $overwrite bool - overwrite existing code files
     * @return ObjectAdapter ObjectAdapter - returns the appropriate object adapter
     */
    protected function addToModx($adapter, $fields, $overwrite = false) {
        /* These are here for LexiconHelper */
        // include 'pluginadapter.class.php';
        // include 'chunkadapter.class.php';
        // include 'propertysetadapter.class.php';
        // include 'snippetadapter.class.php';
        // include 'templateadapter.class.php';
        // include 'templatevaradapter.class.php'

        /* @var $o ObjectAdapter */
        // include 'objectadapter.class.php';
        $o = new $adapter($this->modx, $this->helpers, $fields);
        $o->addToMODx($overwrite);
        return $o;

    }

    /**
     * Create intersects for many-to-many relationships between objects
     * in MODX
     */
    public function createIntersects() {
        /* Connect TVs to Templates */
        $o = ObjectAdapter::$myObjects;
        $intersects = $this->modx->getOption('tvResolver', $o, array());
        $this->helpers->createIntersects('modTemplateVarTemplate', $intersects);

        /* Connect Plugins to Events */
        $intersects = $this->modx->getOption('pluginResolver', $o, array());
        $this->helpers->createIntersects('modPluginEvent', $intersects);

        /* Connect Elements to Property Sets */
        $intersects = $this->modx->getOption('propertySetResolver', $o, array());
        $this->helpers->createIntersects('modElementPropertySet', $intersects);
    }


    /**
     * Create the various resolver files needed to build the extra.
     * Calls the static method of the appropriate adapter object.
     * @param int $mode constant - if $mode is Export, existing
     * resolver files will be updated.
     */
    public function createResolvers($mode = MODE_BOOTSTRAP) {
        $dir = $this->myPaths['targetResolve'];
        $o = ObjectAdapter::$myObjects;

        /* Category Resolver */
        $intersects = $this->modx->getOption('categories', $o, array());
        $needResolver = false;
        /* see if we really need the resolver */
        foreach($intersects as $k => $fields) {
            /* see if parent is set to something other than 0 */
            if ($fields['parent']) {
                $needResolver = true;
            }
        }
        if ($needResolver) {
            CategoryAdapter::createResolver($dir, $intersects, $this->helpers, $mode);
        }

        /* Resource Resolver ( */
        $intersects = $this->modx->getOption('resourceResolver', $o, array());
        ResourceAdapter::createResolver($dir, $intersects, $this->helpers, $mode);

        /* TV Resolver */
        $intersects = $this->modx->getOption('tvResolver', $o, array());
        TemplateVarAdapter::createResolver($dir, $intersects, $this->helpers, $mode);

        /* Plugin Resolver */
        $intersects = $this->modx->getOption('pluginResolver', $o, array());
        $newEvents = $this->modx->getOption('newSystemEvents', $o, array());
        PluginAdapter::createResolver($dir, $intersects, $this->helpers, $newEvents, $mode);

        /* Property Set Resolver */
        $intersects = $this->modx->getOption('propertySetResolver', $o, array());
        PropertySetAdapter::createResolver($dir, $intersects, $this->helpers, $mode);

        /* extra resolvers */
        /* These user-specific resolvers never get updated, even on Export */
        $extraResolvers = $this->modx->getOption('resolvers', $this->props, array());
        $dir = $this->myPaths['targetResolve'];
        foreach ($extraResolvers as $k => $name) {
            $name = ($name == 'default')
                ? $this->packageNameLower
                : $name;
            $name = strtolower($name);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_resolver')
                . ': ' . $name);
            $tpl = $this->helpers->getTpl('genericresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                    '[MyComponentProject]' .
                        $this->modx->lexicon('mc_empty_genericresolver'));
                continue;
            }
            $fileName = $name . '.resolver.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $name . ' ' .
                    $this->modx->lexicon('mc_resolver')
                    . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }

        /* extra validators - also never get updated */
        $extraValidators = $this->modx->getOption('validators', $this->props, array());
        $dir = $this->myPaths['targetValidate'];
        foreach ($extraValidators as $k => $name) {
            $name = ($name == 'default')
                ? $this->packageNameLower
                : $name;
            $name = strtolower($name);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_validator')
             . ': ' . $name);
            $tpl = $this->helpers->getTpl('genericvalidator.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                    '[MyComponentProject] ' .
                        $this->modx->lexicon('mc_empty_genericvalidator'));
                continue;
            }
            $fileName = $name . '.validator.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $name . ' ' .
                    $this->modx->lexicon('mc_validator')
                  . ' ' . $this->modx->lexicon('mc_already_exists'));
            }
        }
    }


    /**
     * Create all transport files necessary for the build
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function createTransportFiles($mode = MODE_BOOTSTRAP) {
        ElementAdapter::createTransportFiles($this->helpers, $mode);
        ResourceAdapter::createTransportFiles($this->helpers, $mode);
        SystemSettingAdapter::createTransportFiles($this->helpers, $mode);
        SystemEventAdapter::createTransportFiles($this->helpers, $mode);
        MenuAdapter::createTransportFiles($this->helpers, $mode);
        ContextAdapter::createTransportFiles($this->helpers, $mode);
        ContextSettingAdapter::createTransportFiles($this->helpers, $mode);

        if (is_dir($this->myPaths['targetBuild'] . 'subpackages')) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n    " .
                $this->modx->lexicon('mc_processing_subpackages'));
            $o = new SubpackageAdapter();
            $o->createSubpackages($this->modx, $this->helpers, $this->myPaths['targetBuild'], $mode);
        }

    }

    /** Create main build.transport.php, build.config.php and
     *  starter project config files, (optionally) lexicon files, doc file,
     *  readme.md -- files only, creates no objects in the DB */
    public function createBasics() {

        /* Transfer build.transport.php and build.config.php files */

        $dir = $this->myPaths['targetBuild'];
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_creating_build_files'));
        $fileName = 'build.transport.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl($fileName);
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                $this->modx->lexicon('mc_already already exists'));
        }
        /* transfer build.config.php from tpl chunk/file to target _build dir. */
        $fileName = 'build.config.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl('build.config.php');
            $tpl = str_replace('[[+packageNameLower]]', $this->packageNameLower, $tpl);
            $dir = $this->myPaths['targetBuild'];
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
                $this->modx->lexicon('mc_already_exists'));
        }

        /* transfer example.config.php from tpl chunk/file to target _build dir. */
        $fileName = 'example.config.php';
        $dir = $this->myPaths['targetBuild'] . 'config/';
        if (!file_exists($dir . $fileName)) {
            $tpl = $this->helpers->getTpl('example.config.php');
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
                $this->modx->lexicon('mc_already_exists'));
        }

        $this->updateProjectConfig(MODE_BOOTSTRAP);

        $fileName = 'current.project.php';
        $tpl = "<?php
   /** MyComponent Current Project
    *  Change this file whenever you work on another project
    *
    *  This should be set to the lowercase name of your package and
    *  Should match the \$packageNameLower value in the Project Config
    *  file (which must be named {packageNameLower}.config.php)
    * */

\$currentProject = '" . $this->packageNameLower. "';";
    if (! file_exists($dir . $fileName)) {
        $this->helpers->writeFile($dir, $fileName, $tpl);
    } else {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
            $this->modx->lexicon('mc_already_exists'));
    }

        /* Create language directories and files specified in project config */
        if (isset ($this->props['languages']) && !empty($this->props['languages'])) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_lexicon_files'));
            $lexiconBase = $this->myPaths['targetCore'] . 'lexicon/';
            foreach ($this->props['languages'] as $language => $languageFiles) {
                $dir = $lexiconBase . $language;
                $files = !empty($languageFiles)
                    ? $languageFiles
                    : array();
                foreach ($files as $file) {
                    $fileName = $file . '.inc.php';
                    if (!file_exists($dir . '/' . $fileName)) {
                        $tpl = $this->helpers->getTpl('phpfile.php');
                        $tpl = str_replace('[[+elementName]]', $language . ' ' . $file . ' topic', $tpl);
                        $tpl = str_replace('[[+description]]', $language . ' ' . $file . ' topic lexicon strings', $tpl);
                        $tpl = str_replace('[[+elementType]]', 'lexicon file', $tpl);
                        $tpl = $this->helpers->replaceTags($tpl);
                        $this->helpers->writeFile($dir, $fileName, $tpl);
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $language . '/' . $fileName . ' ' . $this->modx->lexicon('mc_file')
                         . ' ' . $this->modx->lexicon('mc_already_exists'));
                    }

                }
            }
        }

        $docs = isset($this->props['docs'])
            ? $this->props['docs']
            : array();

        if (!empty($docs)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_doc_files'));
            $toDir = $this->myPaths['targetCore'] . 'docs/';
            foreach ($docs as $doc) {
                if (!file_exists($toDir . $doc)) {
                    $tpl = $this->helpers->getTpl($doc);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($toDir, $doc, $tpl);
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $doc . ' ' .
                        $this->modx->lexicon('mc_file')
                        . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }
            }
        }
        $readmeMd = isset($this->props['readme.md'])
            ? $this->props['readme.md']
            : false;
        if ($readmeMd) {
            if (!file_exists($this->myPaths['targetRoot'] . 'readme.md')) {
                $tpl = $this->helpers->getTpl('readme.md');
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($this->myPaths['targetRoot'], 'readme.md', $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    readme.md file ' .
                    $this->modx->lexicon('mc_already_exists'));
            }

        }

        $hasAssets = $this->modx->getOption('hasAssets', $this->props, false);
        $doJsMin = $this->modx->getOption('minifyJS', $this->props, false);
        $this->createAssetsDirs();
        $this->createInstallOptions();
        $this->createCMPFiles();
        $this->createClassFiles();

        if ($hasAssets && $doJsMin) {
            /* copy minimizer classes to project _build/utilities directory
               and create/update js-min files if required */

            $minimizers = array('jsminplus.class.php','jsmin.class.php');

            foreach($minimizers as $minimizer) {
                $path = $this->modx->getOption('mc.core_path',
                    null, $this->modx->getOption('core_path') .
                        'components/mycomponent/') . 'model/mycomponent/' . $minimizer;
                if (file_exists($path)) {
                    $fileContent = file_get_contents($path);
                }
                if (!empty($fileContent)) {
                    if (!file_exists($this->myPaths['targetBuild'] .
                        'utilities/' . $minimizer)) {
                        $this->helpers->writeFile($this->myPaths['targetBuild'] .
                            'utilities', $minimizer, $fileContent);
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                            $minimizer . ' ' .
                            $this->modx->lexicon('mc_already_exists'));
                    }
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '    ' .
                        $this->modx->lexicon('mc_jsmin_nf') . $minimizer);
                }
            }
            $this->helpers->minifyJs();
        }

        return true;
    }

    /**
     * Update the local project config file from the one in the MyComponent directory
     *
     * @param int $mode - MODE_BOOTSTRAP, MODE_EXPORT, MODE_REMOVE
     */
    public function updateProjectConfig($mode = MODE_BOOTSTRAP) {
        $fileName = $this->packageNameLower . '.config.php';
        $dir = $this->myPaths['targetBuild'] . 'config/';

        if (!file_exists($dir . $fileName) || $mode == MODE_EXPORT) {
            $msg = "\n\n " . '/' . '*' . "               DO NOT EDIT THIS FILE\n\n  Edit the file in the MyComponent config directory\n  and run ExportObjects\n\n *" . '/' . "\n\n";
            $search = '<' . '?' . 'php';
            $mcConfigDir = $this->mcRoot . '_build/config/';
            if (file_exists($mcConfigDir . $fileName)) {
                $tpl = file_get_contents($mcConfigDir . $fileName);
                if (!empty ($tpl)) {
                    $tpl = str_replace($search, $search . $msg, $tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                }
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_file_nf')
                    . ': ' . $mcConfigDir . $fileName);
            }
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
                $this->modx->lexicon('mc_already_exists'));
        }

    }

    /** Create assets directories and (optionally) empty css file, and js files
     * if set in project config file. JS files are skipped if we're doing a CMP */

    public function createAssetsDirs() {
        $hasAssets = $this->modx->getOption('hasAssets', $this->props, false);
        if (! $hasAssets) {
            return;
        }
        $optionalDirs = $this->modx->getOption('assetsDirs', $this->props, array());
        if (! is_array($optionalDirs)) {
            $optionalDirs = array();
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_assets_dirs_not_an_array'));
        }
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_creating_assets_directories'));
        /* Create Assets dir even if it will be empty */
        $targetDir = $this->myPaths['targetAssets'];
        if (! is_dir($targetDir)) {
            mkdir($targetDir, $this->dirPermission, true);
        }
        foreach ($optionalDirs as $dir => $val) {
            $targetDir = $this->myPaths['targetAssets'] . $dir;
            if ($val) {
                if (!is_dir($targetDir)) {
                    if (mkdir($targetDir, $this->dirPermission, true)) {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                            $this->modx->lexicon('mc_created')
                            . ' ' . $targetDir .
                            $this->modx->lexicon('mc_directory'));
                    }
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    Assets/' . $dir . ' ' .
                        $this->modx->lexicon('mc_directory')
                        . ' ' . $this->modx->lexicon('mc_already_exists'));
                }
            }
            $doCmp = $this->helpers->getProp('createCmpFiles', false);
            if ( $val && ($dir == 'css' || $dir == 'js')) {
                /* Don't do JS files here unless CreateCmpFiles is false */
                $files = array();
                if ($dir == 'js' && (! $doCmp)) {
                    $files = $this->helpers->getProp('jsFiles', array());
                } elseif ($dir == 'css') {
                    $files = $this->helpers->getProp('cssFiles', array());
                }

                /* write JS and CSS files listed in config file */
                foreach($files as $file) {
                    $path = $this->myPaths['targetAssets'] . $dir;
                    if (!file_exists($path . '/' . $file)) {
                        $tpl = $this->helpers->getTpl($dir);
                        $tpl = $this->helpers->replaceTags($tpl);
                        $this->helpers->writeFile($path, $file, $tpl);
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                            $this->modx->lexicon('mc_already_exists'));
                    }
                }
           }
        }
    }

    /** Create PHP and JS CMP files specified in project config file.
     */
    public function createCMPFiles() {
        /* Return if 'createCmpFiles' is false */
        $createCmpFiles = $this->helpers->getProp('createCmpFiles');
        if (empty($createCmpFiles)) {
            return;
        }
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_writing_cmp_files'));

        /* Get the files to create from the project config file */
        $actionFile = $this->helpers->getProp('actionFile', array());
        $cssFile = $this->helpers->getProp('cssFile', array());
        $processors = $this->helpers->getProp('processors', array());
        $controllers = $this->helpers->getProp('controllers', array());
        $connectors = $this->helpers->getProp('connectors', array());
        $jsFiles = $this->helpers->getProp('cmpJsFiles', array());
        $cmpTemplateFiles = $this->helpers->getProp('cmpTemplates', array());
        /* Create CMP class file */
        $this->createCmpClassFile();

        /* Create CMP CSS file */
        if (!empty($cssFile)) {
            $this->createCmpCssFile($cssFile);
        }

        /* Create controllerrequest file */
        $this->createTemplateFiles($cmpTemplateFiles);

        /* Create main action file (index.php) */
        if (!empty($actionFile)) {
            $this->createActionFile($actionFile);
        }
        /* Create processor files */
        if (!empty($processors)) {
            $this->createProcessorFiles($processors);

        }
        /* Create controller files */
        if (!empty($controllers)) {
            $this->createControllerFiles($controllers);
        }

        /* Create connector files */
        if (!empty($connectors)) {
            $this->createConnectorFiles($connectors);
        }

        /* Create CMP JS Files */
        if (!empty($jsFiles)) {
            $this->createCmpJsFiles($jsFiles);
        }
    }

    /**
     * Create the main CMP class file
     */
    public function createCmpClassFile() {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_cmp_class_file'));
        $dir = $this->myPaths['targetCore'] . 'model/' . $this->packageNameLower;
        $fileName = $this->packageNameLower . '.class.php';

        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl('cmp.classfile.php');
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $fileName . ' ' .
                $this->modx->lexicon('mc_already_exists'));
        }
    }

    /**
     * Create the main CMP .CSS file
     *
     * @param $cssFile
     */
    public function createCmpCssFile($cssFile) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_cmp_css_file'));
        $dir = $this->myPaths['targetAssets'] . 'css';
        $fileName = $cssFile;

        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl('cmp.mgr.css');
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $fileName . ' ' .
                $this->modx->lexicon('mc_already_exists'));
        }
    }

    /**
     * Create the template files
     *
     */
    public function createTemplateFiles($templates) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_template_files'));
        $dir = $this->myPaths['targetCore'] . 'templates';
        foreach ($templates as $template) {
            $couple = explode(':', $template);
            $content = isset($couple[1])? $couple[1] : '';
            $fileName = $couple[0];
            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $content;
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName . '.tpl', $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $fileName . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /**
     * Create the main CMP action file
     *
     * @param $actionFile
     */
    public function createActionFile($actionFile) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    '
            . $this->modx->lexicon('mc_creating_cmp_index_file'));
        $dir = $this->myPaths['targetCore'];
        $file = $actionFile;
        $tpl = $this->helpers->getTpl('cmp.actionfile.php');
        $tpl = $this->helpers->replaceTags($tpl);
        if (!file_exists($dir . $file)) {
            $this->helpers->writeFile(rtrim($dir, '/'), $file, $tpl);
        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                $this->modx->lexicon('mc_already_exists'));
        }
    }

    /**
     * Create the CMP processor files
     *
     * @param $processors
     */
    public function createProcessorFiles($processors) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    '
            . $this->modx->lexicon('mc_creating_cmp_processors'));
        $processorDir = $this->myPaths['targetProcessors'];

        foreach ($processors as $processor) {
            $couple = explode(':', $processor);
            $dir = $processorDir . $couple[0];
            $file = $couple[1] . '.class.php';
            if (!file_exists(rtrim($dir, '/') . '/' . $file)) {
                $tpl = $this->getProcessorTpl($dir, $file);
                $this->helpers->writeFile(rtrim($dir, '/'), $file, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /**
     * Get and customize a processor's content
     *
     * @param $dir
     * @param $file
     * @return mixed|string
     */
    public function getProcessorTpl($dir, $file) {

        $processorName = '';
        $matches = array();
        $processorClass = '';
        $elementName = '';


        $tpl = $this->helpers->getTpl('cmp.' . $file);
        if (empty($tpl)) {
            $tpl = $this->helpers->getTpl('cmp.processor.class.php');
        }

        /* extract the actual processor name from the filename */
        $pattern = '/^([a-z]+)/';

        preg_match($pattern, $file, $matches);
        if (isset($matches[1])) {
            $processorName = $matches[1];
        }

        /* set up arrays of possible processors and elements */
        $processors = array('GetList','Create','Update','Duplicate','Remove','Import','Export');
        $elements = array('resource', 'chunk', 'snippet', 'plugin', 'template', 'tv', 'article', 'templateVar');

        /* look for them */

        foreach($processors as $processor) {
            if (stripos($processorName, $processor) !== false) {
                $processorClass = 'modObject' . $processor . 'Processor';
                break;
            }
        }
        foreach ($elements as $element) {
            if ($element == 'tv') {
                $element = 'templateVar';
            }
            if (stripos($dir, $element) !== false) {
                $elementName = $element;
                break;
            }
        }
        $replaceFields = array(
            'mc_element' => $elementName,
            'mc_Element' => ucfirst($elementName),
            'mc_ProcessorType' => $this->packageName . ucfirst($elementName)
                . ucfirst($processorName),
        );
        $tpl = $this->helpers->replaceTags($tpl, $replaceFields);
        $tpl = $this->helpers->replaceTags($tpl);

        if (!empty($processorClass)) {
            $tpl = str_replace('modProcessor', $processorClass, $tpl);
        }

        /* adjust for 'name' field of templates, categories, articles, and resources */
        if (stripos($elementName, 'template') !== false) {
            $tpl = str_replace("'name'", "'templatename'", $tpl);
        }
        if (stripos($elementName, 'category') !== false) {
            $tpl = str_replace("'name'", "'category'", $tpl);
        }
        if (stripos($elementName, 'resource') !== false) {
            $tpl = str_replace("'name'", "'pagetitle'", $tpl);
        }
        if (stripos($elementName, 'article') !== false) {
            $tpl = str_replace("'name'", "'pagetitle'", $tpl);
            $tpl = str_replace("'modArticle'", "'Article'", $tpl);
        }
        return $tpl;
    }

    /**
     * Create CMP controllers specified in the project config file
     *
     * @param $controllers array
     */
    public function createControllerFiles($controllers) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_cmp_controllers'));
        $controllerDir = $this->myPaths['targetControllers'];
        foreach ($controllers as $controller) {
            $tpl = '';
            $couple = explode(':', $controller);
            $dir = $controllerDir . $couple[0];
            $file = $couple[1];
            if (!file_exists(rtrim($dir, '/') . '/' . $file)) {
                $tpl = $this->helpers->getTpl('cmp.controllerhome');
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile(rtrim($dir, '/'), $file, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /**
     * Create connectors specified in the project config file
     *
     * @param $connectors
     */
    public function createConnectorFiles($connectors) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_cmp_connectors'));
        $dir = $this->myPaths['targetConnectors'];
        $tpl = $this->helpers->getTpl('cmp.connectorfile');
        $tpl = $this->helpers->replaceTags($tpl);
        foreach ($connectors as $connector) {
            $file = $connector;
            if (!file_exists($dir . $file)) {
                $this->helpers->writeFile(rtrim($dir, '/'), $file, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /**
     * Create all CMP JS files specified in the project config
     *
     * @param $jsFiles
     */
    public function createCmpJsFiles($jsFiles) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $this->modx->lexicon('mc_creating_cmp_js_files'));
        $tpl = $this->helpers->getTpl('js');
        $jsDir = $this->myPaths['targetJs'];
        foreach ($jsFiles as $jsFile) {
            $couple = explode(':', $jsFile);
            $dir = $jsDir . $couple[0];
            $file = $couple[1];
            if (!file_exists($dir . '/' . $file)) {
                if ($file == $this->packageNameLower . '.class.js') {
                    /* Main JS file */
                    $tpl = $this->helpers->getTpl('cmp.defaultjs');
                } elseif (strpos($file, 'grid') !== false) {
                    $tpl = $this->getGridTpl($file);

                } else {
                    /* Look for a tpl chunk with the name filename.tpl */
                    $tpl = $this->helpers->getTpl('cmp.' . $file);
                }
                if (empty($tpl)) {
                    /* default -- use generic JS tpl */
                    $tpl = $this->helpers->getTpl('js');
                }
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile(rtrim($dir, '/'), $file, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' . $file . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }

        }
    }

    /**
     * Get and customize the content of any grid JS files specified
     * in the project config file
     *
     * @param $file
     * @return mixed
     */
    function getGridTpl($file) {
        $tpl = $this->helpers->getTpl('cmp.grid');
        $elements = array('snippet', 'chunk', 'plugin', 'template', 'tv', 'templatevar');
        $name = '';
        foreach($elements as $element) {
            if (strpos($file, $element) !== false) {
                $name = $element;
            }
        }
        $tpl = str_replace('[[+element]]', $name, $tpl);
        $tpl = str_replace('[[+Element]]', ucfirst($name), $tpl);


        return $tpl;
    }

    /**
     * Get and customize any example changeCategory processors if
     * specified in the project config file
     *
     * @param $file
     * @return mixed
     */
    function getChangeCategoryTpl($file) {
        $tpl = $this->helpers->getTpl('cmp.changecategory');
        $elements = array(
            'snippet',
            'chunk',
            'plugin',
            'template',
            'tv',
            'templatevar'
        );
        $name = '';
        foreach ($elements as $element) {
            if (strpos($file, $element) !== false) {
                $name = $element;
            }
        }
        $name = $name == 'tv'
            ? 'templateVar'
            : $name;
        $tpl = str_replace('[[+element]]', $name, $tpl);
        $tpl = str_replace('[[+Element]]', ucfirst($name), $tpl);
        return $tpl;
    }

    /********************************
     * End of CMP Section
     ********************************/


    /** Create example file for user input during install
     * if set in project config file */

    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (!empty($iScript)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_install_options'));
            $dir = $this->targetRoot . '_build/install.options';
            $fileName = 'user.input.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /** Create validators if set in project config file
     * */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_validators'));
            $dir = $this->targetRoot . '_build/validators';

            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $fileName = $this->packageNameLower . '.' . 'validator.php';
                } else {
                    $fileName = $validator . '.' . 'validator.php';
                }
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('genericvalidator.php');
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }
            }
        }
    }


    /** Create "starter" class files specified in project config
     *  file unless we're doing a CMP */

    public function createClassFiles() {
        /* @var $element modElement */
        $classes = $this->modx->getOption('classes', $this->props, array());
        $classes = !empty($classes)
            ? $classes
            : array();
        if (!empty($classes)) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_class_files'));
            $baseDir = $this->myPaths['targetCore'] . 'model';
            foreach ($classes as $className => $data) {
                if ($className == 'methods') {
                    continue;
                }
                $data = explode(':', $data);
                if (!empty($data[1])) {
                    $dir = $baseDir . '/' . $data[0];
                    $fileName = $data[1];
                } else { /* no directory */
                    $dir = $baseDir. '/' . $this->packageNameLower;
                    $fileName = $data[0];
                }
                $fileName = strtolower($fileName) . '.class.php';
                $doCmp = $this->modx->getOption('createCmpFiles', $this->props, false);
                if ((strpos($fileName, $this->packageNameLower) !== false)
                    && $doCmp ){
                    /* Don't create class file if it's a CMP */
                    continue;
                }
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl);
                    $tpl = str_replace('[[+className]]', $className, $tpl);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $methods = $this->getMethods($className);
                    if ($methods !== false) {
                        $tpl = str_replace('/* [[+code]] */', $methods, $tpl);
                    }
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }

            }
        }
    }

    public function getMethods($className) {
        if (! isset($this->props['classes']['methods'][$className])) {
            return false;
        }
        $methodString = '';
        $methods = $this->props['classes']['methods'][$className];
        foreach ($methods as $method) {
            if (strpos($method, 'function') === false) {
                $method= "public function " . $method;
            }
            $methodString .= "\n    " . $method  . " {\n\n    }\n";
        }
        return $methodString;
    }
    /* *****************************************************************************
       Export Objects
    ***************************************************************************** */

    /**
     * Export all MODX objects in the package to the appropriate
     * code files. Most objects are selected by namespace or category,
     * with the exception of resources, which are specified in the project
     * config file.
     *
     * Only object types in the 'process' member of the config file are
     * processed.
     */
    public function exportComponent() {
        //Only run if MC is installed
        if (!$this->isMCInstalled()) {
            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[MyComponentProject] ' .
                $this->modx->lexicon('mc_mycomponent_not_installed_export'));
            return;
        }
        ObjectAdapter::$myObjects = array();

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
         . ': ' .  $this->modx->lexicon('mc_export_objects'));
        $mode = MODE_EXPORT;

        $toProcess = $this->modx->getOption('process', $this->props, array());
        $this->createNamespaces($mode);

        if (in_array('contexts', $toProcess)) {
            $this->createContexts($mode);
        }

        if (in_array('contextSettings', $toProcess)) {
            $this->createContextSettings($mode);
        }

        if (in_array('systemSettings', $toProcess)) {
            $this->createNewSystemSettings($mode);
        }
        if (in_array('systemEvents', $toProcess)) {
            $this->createNewSystemEvents($mode);
        }
        $this->createCategories($mode);

        if (in_array('resources', $toProcess)) {
            $this->createResources($mode);
        }

        $this->createResolvers($mode);

        $this->createTransportFiles($mode);

        if ($this->modx->getOption('minifyJS', $this->props, false)) {
            $this->helpers->minifyJs();
        }

        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
        $this->modx->lexicon('mc_updating_project_config'));
        $this->updateProjectConfig(MODE_EXPORT);
    }


    /* *****************************************************************************
        Import Objects
     ***************************************************************************** */


    /**
     * Create and overwrites MODX Objects based on the elements
     * in the 'elements' member of the Project config
     *
     *
     * @param array $toProcess - array of object to import
     * @param string $directory - directory to place objects in
     * @param bool $dryRun -- if set, will just report what it would have done
     */
    public function importObjects($toProcess, $directory = '', $dryRun = true) {
        if (empty($directory)) {
            $directory = $this->myPaths['targetCore'] . 'elements/';
        }
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
         . ': ' . $this->modx->lexicon('mc_import_objects'));
        $toProcess = explode(',', $toProcess);
        foreach ($toProcess as $elementType) {
            $class = 'mod' . ucfirst(substr($elementType, 0, -1));
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing')
                . ' ' . $elementType);
            $elements = $this->modx->getOption($elementType,$this->props['elements'], array());
            foreach ($elements as $element => $fields) {
                if (isset($fields['filename'])) {
                    $fileName = $fields['filename'];
                } else {
                    $fileName =
                        $this->helpers->getFileName($element, $class);
                }
                $alias = $this->helpers->getNameAlias($class);
                $object = $this->modx->getObject($class,
                    array($alias => $element));
                $static = $object->get('static');
                if (!empty($static)) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                        $this->modx->lexicon('mc_processing_static_element')
                        . $element);
                    $path = MODX_BASE_PATH . $object->get('static_file');

                } else {
                    $path = $directory . $elementType . '/' . $fileName;
                }
                // $dir = $directory . $elementType . '/';
                if (file_exists($path)) {
                    if ($object) {
                        $content = file_get_contents($path);
                        if (!empty($content)) {
                            if ($dryRun) {
                                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                                    '    ' .
                                        $this->modx->lexicon('mc_would_be_updating')
                                . ': ' . $element);
                            } else {
                                $object->setContent($content);
                                $propsDir = $this->targetRoot . '_build/data/properties/';
                                $propsFileName = $this->helpers->getFileName($element, $class, 'properties');
                                $propsFile = $propsDir . $propsFileName;
                                if (file_exists($propsFile)) {
                                    $props = include ($propsFile);
                                    if (is_array($props)) {
                                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                                            '    ' .
                                        $this->modx->lexicon('mc_setting_properties_for')
                                        . ' ' . $element);
                                        $object->setProperties($props);
                                    }
                                }

                                if ($object->save()) {
                                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                                        '    ' . $this->modx->lexicon('mc_updated')
                                        . ': ' . $element);
                                }
                            }
                        }
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                            $this->modx->lexicon('mc_element_nf')
                            . ': ' . $element);
                    }
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_file_nf')
                        . ': ' . $fileName);
                }
            }

        }

        /* Do Resources - use exportResources array member for pagetitles */

        $pageTitles = $this->modx->getOption('exportResources', $this->props, array());
        if (count($pageTitles) > 0) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing')
                . ' ' . 'Resources');
        }
        foreach($pageTitles as $pageTitle) {
            if ($dryRun) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    '    ' .
                    $this->modx->lexicon('mc_would_be_updating')
                    . ': ' . $pageTitle);
            } else {
                $fileName = $this->helpers->getFileName($pageTitle, 'modResource');
                $dir = $this->myPaths['targetResources'];
                $content = file_get_contents($dir . $fileName);
                if (!empty($content)) {
                    $resource = $this->modx->getObject('modResource', array('pagetitle' => $pageTitle));
                    if ($resource) {
                        $resource->setContent($content);
                        if ($resource->save()) {
                            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                                '    ' . $this->modx->lexicon('mc_updated')
                                . ': ' . $pageTitle);
                        }
                    }
                }
            }

        }

    }

    /* *****************************************************************************
        Development Utilities
     ***************************************************************************** */
    /**
     * Utility function to remove all objects from MODX during development
     *
     * @param bool $removeFiles - if set, files will be removed too
     */
    public function removeObjects($removeFiles = false) {
        $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
            . ': ' .
            $this->modx->lexicon('mc_remove_objects'));
        $oldLogLevel = $this->modx->setLogLevel(modX::LOG_LEVEL_ERROR);
        $this->createObjects(MODE_REMOVE);
        if ($removeFiles) {
            $dir = $this->targetRoot;
            $tr = $this->helpers->getProp('targetRoot');
            if (empty($dir) || (! ($this->targetRoot == $tr))) {
                session_write_close();
                die('mismatched or empty targetRoot -- aborting removeObjects');
            }
            $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_removing_project_files'));
            $this->modx->setLogLevel($temp);
            $this->rrmdir($dir);
            if (! is_dir($dir)) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_files_and_directories_removed'));
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_vc_files_may_remain'));
            }
        }
        $this->modx->setLogLevel($oldLogLevel);
        $cm = $this->modx->getCacheManager();
        $cm->refresh();
    }

    /** recursive remove dir function.
     *  Removes a directory and all its children */
    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $prefix = substr($object, 0, 4);
                        if ($prefix == '.git' || $prefix == '.svn') {
                            continue;
                        }
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        $prefix = substr($object, 0, 4);
                        if ( $prefix != '.git' && $prefix != '.svn') {
                            @unlink($dir . "/" . $object);
                        }
                    }
                }
            }
            reset($objects);
            $success = @rmdir($dir);
        }
    }
}

