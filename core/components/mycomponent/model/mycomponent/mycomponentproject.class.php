<?php

if (!defined('MODE_BOOTSTRAP')) {
    define('MODE_BOOTSTRAP', 0);
    define('MODE_EXPORT', 1);
    define('MODE_IMPORT', 2);
    define('MODE_REMOVE', 3);
}


class MyComponentProject {
    /* @var $modx modX */
    public $modx;


    public $myPaths = array();
    public $packageNameLower = '';
    public $targetRoot = '';
    public $mcRoot = '';
    public $mcCore = '';
    public $props = array();
    /* @var $helpers Helpers */
    public $helpers;
    public $dirPermission;
    /* Array of object names and fields created for bootstrap */
    protected $bootstrapObjects;
    /* Array of object names and fields created for exportObjects */
    protected $exportObjects;
    protected $configPath;


    /* *****************************************************************************
       Property Getter and Setters
    ***************************************************************************** */
    /**
     * Convenience method for determining if MyComponent is installed.
     *
     * @return boolean - True, if MC is installed. False, if not.
     */
    public function isMCInstalled() {
        return $this->myPaths['mcCore'] != '';
    }

    /* *****************************************************************************
       Construction and Support Functions (in MODxObjectAdapter)
    ***************************************************************************** */
    public function __construct(&$modx) {

        if (!defined('MODE_BOOTSTRAP')) {
            die("bootstrap not defined");
        }
        $this->modx =& $modx;
    }


    public function init($scriptProperties = array(), $currentProject = '') {

        require dirname(__FILE__) . '/mcautoload.php';
        spl_autoload_register('mc_auto_load');

        if (empty($currentProject)) {
            $currentProjectPath = $this->modx->getOption('mc.root', null,
                $this->modx->getOption('core_path') . 'components/mycomponent/') . '_build/config/current.project.php';
            if (file_exists($currentProjectPath)) {
                include $currentProjectPath;
            } else {
                die('Could not find current.project.php file at: ' . $currentProjectPath);
            }
        }
        if (empty($currentProject)) {
            die('No current Project Set');
        }

        $projectConfigPath = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/') .
            '_build/config/' . strtoLower($currentProject) . '.config.php';

        if (file_exists($projectConfigPath)) {
            $properties = include $projectConfigPath;
        } else {
            die('Could not find Project Config file at: ' . $projectConfigPath);
        }

        /* Make sure that we get usable values */
        if (!is_array($properties) or empty($properties)) {
            die('Config File was not set up correctly: ' . $projectConfigPath);
        }

        /* Properties sent in method call will override those in Project Config file */
        $properties = array_merge($properties, $scriptProperties);
        $this->packageNameLower = $properties['packageNameLower'];

        $this->mcRoot = isset($properties['mycomponentRoot'])
            ? $properties['mycomponentRoot']
            : '';
        if (empty($this->mcRoot)) {
            die('mcRoot is not set in Project Config: ' . $projectConfigPath);
        }
        if (!is_dir($this->mcRoot)) {
            die('mcRoot set in project config is not a directory: ' . $projectConfigPath);
        }
        $this->mcRoot = $this->modx->getOption('mc.root', null,
            $this->modx->getOption('core_path') . 'components/mycomponent/');

        $this->targetRoot = $this->modx->getOption('targetRoot', $properties, '');

        if (empty($this->targetRoot)) {
            die('targetRoot is not set in project config file');
        }
        $this->props = $properties;
        $this->initPaths();
        // include 'helpers.class.php';
        $helpers = new Helpers($this->modx, $this->props);
        $this->helpers = $helpers;
        $this->helpers->init();

        $this->dirPermission = $this->props['dirPermission'];
        $this->updateProjectsFile($projectConfigPath);
        $this->configPath = $projectConfigPath;
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
            "\n" . $this->modx->lexicon('mc_project')
            . ': ' . $this->props['packageName']);
    }


    /**
     * Updates the file with the names and project config paths of each project
     * for use by the UI
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
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_updated_projects_file'));
            }

        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_created_projects_file'));
            $content = $header . "    '" . $newContent . $footer;
            $fp = fopen($projectsFile, 'w');
            fwrite($fp, $content);
            fclose($fp);

        }
    }


    /**
     * Sets up the Path variables for the Component Project. Called in __construct.
     */
    public function initPaths() {

        $paths = array();
        $name = $this->props['packageNameLower'];
        // @var $ns modNameSpace

        $paths['mcRoot'] = $this->mcRoot;
        $paths['mcCore'] = $this->mcRoot . 'core/components/mycomponent/';
        $paths['mcModel'] = $paths['mcCore'] . 'model/mycomponent/';
        $paths['mcBuild'] = $this->mcRoot . '_build/';
        $paths['mcElements'] = $paths['mcCore'] . 'elements/';
        $paths['mcTpl'] = $paths['mcElements'] . 'chunks/';

        /*  Set the Root path for this Component */
        $paths['targetRoot'] = $this->targetRoot;
        /* Basic Paths */
        $paths['targetCore'] = $paths['targetRoot'] . 'core/components/' . $name . '/';
        $paths['targetControl'] = $paths['targetCore'] . 'controllers/';
        $paths['targetDocs'] = $paths['targetCore'] . 'docs/';
        $paths['targetElements'] = $paths['targetCore'] . 'elements/';
        $paths['targetLexicon'] = $paths['targetCore'] . 'lexicon/';
        $paths['targetModel'] = $paths['targetCore'] . 'model/' . $name . '/';
        $paths['targetProcess'] = $paths['targetCore'] . 'processors/';
        $paths['targetAssets'] = $paths['targetRoot'] . 'assets/components/' . $name . '/';
        $paths['targetCss'] = $paths['targetAssets'] . 'css/';
        $paths['targetJs'] = $paths['targetAssets'] . 'js/';
        $paths['targetImages'] = $paths['targetAssets'] . 'images/';
        $paths['targetBuild'] = $paths['targetRoot'] . '_build/';
        $paths['targetData'] = $paths['targetBuild'] . 'data/';
        $paths['targetResources'] = $paths['targetData'] . '_resources/';
        $paths['targetProperties'] = $paths['targetData'] . 'properties/';
        $paths['targetResolve'] = $paths['targetBuild'] . 'resolvers/';
        $paths['targetValidate'] = $paths['targetBuild'] . 'validators/';

        /* Set myPathc class member */
        $this->myPaths = $paths;

    }

    /* *****************************************************************************
       Bootstrap and Support Functions
    ***************************************************************************** */

    public function bootstrap() {
        /* enable garbage collection() */
        // gc_enable();
        $mode = MODE_BOOTSTRAP;
        if (!$this->isMCInstalled()) { /* Only run if MC is installed */
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject]' . $this->modx->lexicon('mc_mycomponent_not_installed_create_new'));
            return;
        }

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_action')
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

        $this->createTransportFiles($mode);

    }


    /**
     * Creates Adapter Objects
     * Called for both Bootstrap and ExportObjects
     *
     * For MODE_BOOTSTRAP, creates objects in MODX and
     * creates code files for them
     *
     * For MODE_EXPORT, finds objects in MODX
     * and creates code files for them.
     *
     * In both cases the objects fields and resolver
     * fields are written to ObjectAdapter::myObjects
     *
     * for MODE_REMOVE, object is removed from MODX
     *
     * @param int $mode
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
            $temp = $this->modx->setLogLevel(MODX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_removing_objects'));
            $this->modx->setLogLevel($temp);
            $this->createNamespaces($mode);

            /* create category or categories*/
            $this->createCategories($mode);

            $this->createContexts($mode);
        }


    }

    public function createContexts($mode = MODE_BOOTSTRAP) {
        if (!empty($this->props['contexts'])) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_contexts'));
            }
            foreach ($this->props['contexts'] as $context => $fields) {
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

    public function createNamespaces($mode = MODE_BOOTSTRAP) {
        if (!empty($this->props['namespaces'])) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_namespaces'));
            }
            foreach ($this->props['namespaces'] as $namespace => $fields) {
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

    public function createCategories($mode = MODE_BOOTSTRAP) {
        $categories = $this->modx->getOption('categories', $this->props, array());
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_processing_categories'));
        if (empty($categories)) {
            $packageName = $this->modx->GetOption('packageName', $this->props, '');
            if (empty($packageName)) {
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
                    $o->exportElements($toProcess, !empty($this->props['dryRun']));
                } elseif ($mode = MODE_REMOVE) {
                    $o->remove($toProcess);
                }
            }
        }

        /* Update the category.php file if necessary */
        $dir = $this->targetRoot . '_build/config';
        CategoryAdapter::writeCategoryFile($dir, $this->helpers);

    }

    public function createNewSystemSettings($mode = MODE_BOOTSTRAP) {

        $newSystemSettings = $this->modx->getOption('newSystemSettings', $this->props, array());
        if (empty($newSystemSettings)) {
            return;
        }
        if ($mode != MODE_EXPORT) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing_new_system_settings'));
        }
        if ($mode == MODE_BOOTSTRAP) {
            foreach ($this->props['newSystemSettings'] as $key => $fields) {
                if (!isset($fields['key'])) {
                    $fields['key'] = $key;
                }
                // include 'systemsettingadapter.class.php';
                $this->addToModx('SystemSettingAdapter', $fields);
            }

        } elseif ($mode == MODE_EXPORT || $mode == MODE_REMOVE) {
            /* These still come from the project config file  */
            foreach ($newSystemSettings as $setting => $fields) {
                $obj = $this->modx->getObject('modSystemSetting', array('key' => $fields['key']));
                if ($obj) {
                    $fields = $obj->toArray();
                    new SystemSettingAdapter($this->modx, $this->helpers, $fields, $mode);
                    if ($mode == MODE_REMOVE) {
                        $obj->remove();
                    }

                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        '[MyComponentProject]' .
                            $this->modx->lexicon('mc_system_setting_nf')
                            . ': ' . $fields['key']);
                }
            }
        }
    }

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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing_context_settings'));
        }
        if ($mode == MODE_BOOTSTRAP) {
            foreach ($this->props['contextSettings'] as $key => $fields) {
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


    public function createNewSystemEvents($mode = MODE_BOOTSTRAP) {
        $newSystemEvents = $this->modx->getOption('newSystemEvents', $this->props, array());
        if (empty($newSystemEvents)) {
            return;
        }
        if ($mode != MODE_EXPORT) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
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

    public function createElements($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP || $mode == MODE_REMOVE) {
            /* Create elements from the project config file.
             * In Export, they're pulled by category in the
             * CategoryAdapter, so not done here */
            if (isset($this->props['elements']) && !empty($this->props['elements'])) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_elements'));
                $elements = $this->props['elements'];
                foreach ($elements as $element => $elementObjects) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n    " .
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject]' .
                $this->modx->lexicon('mc_illegal_create_elements'));
        }
    }

    public function createResources($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP) {
            if (isset($this->props['resources']) && !empty($this->props['resources'])) {
                /* @var $o ResourceAdapter */
                $o = null;
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_resources'));
                foreach ($this->props['resources'] as $resource => $fields) {
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

    public function createMenus($mode = MODE_BOOTSTRAP) {
        if (!empty($this->props['menus'])) {
            if ($mode != MODE_EXPORT) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                    $this->modx->lexicon('mc_processing_menus'));
            }
            foreach ($this->props['menus'] as $k => $fields) {
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
        // include 'chunkadapter.class.php';
        // include 'propertyset.adapter.class.php';
        // include 'snippetadapter.class.php';
        // include 'template.adapter.class.php';
        // include 'templatevar.adapter.class.php'

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
     * Creates the various resolver files needed to build the extra.
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_resolver')
                . ': ' . $name);
            $tpl = $this->helpers->getTpl('genericresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                    '[MyComponentProject]' .
                        $this->modx->lexicon('mc_empty_genericresolver'));
                continue;
            }
            $fileName = $name . '.resolver.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' ' .
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_validator')
             . ': ' . $name);
            $tpl = $this->helpers->getTpl('genericvalidator.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                    '[MyComponentProject] ' .
                        $this->modx->lexicon('mc_empty_genericvalidator'));
                continue;
            }
            $fileName = $name . '.validator.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' ' .
                    $this->modx->lexicon('mc_validator')
                  . ' ' . $this->modx->lexicon('mc_already_exists'));
            }
        }
    }


    public function createTransportFiles($mode = MODE_BOOTSTRAP) {
        ElementAdapter::createTransportFiles($this->helpers, $mode);
        ResourceAdapter::createTransportFiles($this->helpers, $mode);
        SystemSettingAdapter::createTransportFiles($this->helpers, $mode);
        SystemEventAdapter::createTransportFiles($this->helpers, $mode);
        MenuAdapter::createTransportFiles($this->helpers, $mode);
        ContextAdapter::createTransportFiles($this->helpers, $mode);
        ContextSettingAdapter::createTransportFiles($this->helpers, $mode);

    }

    /** Creates main build.transport.php, build.config.php and
     * starter project config files, (optionally) lexicon files, doc file,
     *  readme.md -- files only, creates no objects in the DB */
    public function createBasics() {

        /* Transfer build.transport.php and build.config.php files */

        $dir = $this->myPaths['targetBuild'];
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_creating_build_files'));
        $fileName = 'build.transport.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl($fileName);
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
                $this->modx->lexicon('mc_already_exists'));
        }

        /* transfer example.config.php from tpl chunk/file to target _build dir. */
        $fileName = 'example.config.php';
        $dir = $this->myPaths['targetBuild'] . 'config/';
        if (!file_exists($dir . $fileName)) {
            $tpl = $this->helpers->getTpl('example.config.php');
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
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
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
            $this->modx->lexicon('mc_already_exists'));
    }

        /* Create language directories and files specified in project config */
        if (isset ($this->props['languages']) && !empty($this->props['languages'])) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
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
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $language . '/' . $fileName . ' ' . $this->modx->lexicon('mc_file')
                         . ' ' . $this->modx->lexicon('mc_already_exists'));
                    }

                }
            }
        }

        $docs = isset($this->props['docs'])
            ? $this->props['docs']
            : array();

        if (!empty($docs)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_doc_files'));
            $toDir = $this->myPaths['targetCore'] . 'docs/';
            foreach ($docs as $doc) {
                if (!file_exists($toDir . $doc)) {
                    $tpl = $this->helpers->getTpl($doc);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($toDir, $doc, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $doc . ' ' .
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
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    readme.md file ' .
                    $this->modx->lexicon('mc_already_exists'));
            }

        }

        $hasAssets = $this->modx->getOption('hasAssets', $this->props, false);
        $doJsMin = $this->modx->getOption('minifyJS', $this->props, false);
        if ($hasAssets && $doJsMin) {
            $path = $this->modx->getOption('mc.core_path',
                null, $this->modx->getOption('core_path') .
                    'components/mycomponent/') . 'model/mycomponent/jsmin.class.php';
            if (file_exists($path)) {
                $fileContent = file_get_contents($path);
            }
            if (!empty($fileContent)) {
                if (!file_exists($this->myPaths['targetBuild'] . 'utilities/jsmin.class.php')) {
                    $this->helpers->writeFile($this->myPaths['targetBuild'] .
                        'utilities', 'jsmin.class.php', $fileContent);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    jsmin class file ' .
                        $this->modx->lexicon('mc_already_exists'));
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_jsmin_nf'));
            }
        }

        $this->createInstallOptions();
        $this->createAssetsDirs();
        $this->createClassFiles();

        return true;
    }
    public function updateProjectConfig($mode = MODE_BOOTSTRAP) {
        /* transfer {$packageNameLower}.config.php from tpl chunk/file to target _build dir. */
        $fileName = $this->packageNameLower . '.config.php';
        $dir = $this->myPaths['targetBuild'] . 'config/';

        if (!file_exists($dir . $fileName) || $mode == MODE_EXPORT) {
            $msg = "\n\n " . '/' . '*' . "               DO NOT EDIT THIS FILE\n\n  Edit the file in the MyComponent config directory\n  and run ExportObjects\n\n *" . '/' . "\n\n";
            $search = '<' . '?' . 'php';
            $mcConfigDir = $this->myPaths['mcRoot'] . '_build/config/';
            if (file_exists($mcConfigDir . $fileName)) {
                $tpl = file_get_contents($mcConfigDir . $fileName);
                if (!empty ($tpl)) {
                    $tpl = str_replace($search, $search . $msg, $tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '    ' .
                    $this->modx->lexicon('mc_file_nf')
                    . ': ' . $mcConfigDir . $fileName);
            }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' '.
                $this->modx->lexicon('mc_already_exists'));
        }

    }

    /** Creates assets directories and (optionally) empty css and js files
     * if set in project config file */
    public function CreateAssetsDirs() {
        if (!$this->props['hasAssets']) {
            return;
        }
        $optionalDirs = !empty($this->props['assetsDirs'])
            ? $this->props['assetsDirs']
            : array();
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
            $this->modx->lexicon('mc_creating_assets_directories'));
        foreach ($optionalDirs as $dir => $val) {
            $targetDir = $this->myPaths['targetAssets'] . $dir;
            if ($val && (!is_dir($targetDir))) {
                if (mkdir($targetDir, $this->dirPermission, true)) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' .
                        $this->modx->lexicon('mc_created')
                        . ' ' . $targetDir .
                        $this->modx->lexicon('mc_directory'));
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Assets/' . $dir . ' ' .
                    $this->modx->lexicon('mc_directory')
                    . ' ' . $this->modx->lexicon('mc_already_exists'));
            }
            if ($dir == 'css' || $dir == 'js') {
                $path = $this->myPaths['targetAssets'] . $dir;
                $fileName = $this->packageNameLower . '.' . $dir;
                if (!file_exists($path . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl($dir);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($path, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '        ' . $fileName . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }

            }
        }
    }


    /** Creates example file for user input during install if set in project config file */
    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (!empty($iScript)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_install_options'));
            $dir = $this->targetRoot . '_build/install.options';
            $fileName = 'user.input.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                    $this->modx->lexicon('mc_already_exists'));
            }
        }
    }

    /** Creates validators if set in project config file */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
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
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }
            }
        }
    }


    /** Creates "starter" class files specified in project config file */
    public function createClassFiles() {
        /* @var $element modElement */
        $classes = $this->modx->getOption('classes', $this->props, array());
        $classes = !empty($classes)
            ? $classes
            : array();
        if (!empty($classes)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_creating_class_files'));
            $baseDir = $this->myPaths['targetCore'] . 'model';
            foreach ($classes as $className => $data) {
                $data = explode(':', $data);
                if (!empty($data[1])) {
                    $dir = $baseDir . '/' . $data[0];
                    $fileName = $data[1];
                } else { /* no directory */
                    $dir = $baseDir. '/' . $this->packageNameLower;
                    $fileName = $data[0];
                }
                $fileName = strtolower($fileName) . '.class.php';
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl);
                    $tpl = str_replace('[[+className]]', $className, $tpl);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                        $this->modx->lexicon('mc_already_exists'));
                }

            }
        }
    }

    /* *****************************************************************************
       Export Objects and Support Functions
    ***************************************************************************** */

    /**
     * This function does the real work of getting the package objects from the
     * MODx database.
     */
    public function exportComponent() {
        /* This should respect $scriptProperties['dryRun'] */
        //Only run if MC is installed
        if (!$this->isMCInstalled()) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject] ' .
                $this->modx->lexicon('mc_mycomponent_not_installed_export'));
            return;
        }


        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
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

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
        $this->modx->lexicon('mc_updating_project_config'));
        $this->updateProjectConfig(MODE_EXPORT);
    }

    /* *****************************************************************************
        Import Objects and Support Functions
     ***************************************************************************** */


    /**
     * Creates and overwrites MODX Objects based on the elements
     * in the 'elements' member of the Project config
     *
     * Will not process static elements
     *
     * @param bool $dryRun -- if set, will just report what it would have done
     */
    public function importObjects($toProcess, $directory = '', $dryRun = true) {
        if (empty($directory)) {
            $directory = $this->myPaths['targetCore'] . 'elements/';
        }
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
         . ': ' . $this->modx->lexicon('mc_import_objects'));
        $toProcess = explode(',', $toProcess);
        foreach ($toProcess as $elementType) {
            $class = 'mod' . ucfirst(substr($elementType, 0, -1));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" .
                $this->modx->lexicon('mc_processing')
                . ' ' . $elementType);
            $elements = $this->modx->getOption($elementType,$this->props['elements'], array());
            foreach ($elements as $element => $fields) {
                if (isset($fields['static']) && !empty($fields['static'])) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' .
                            $this->modx->lexicon('mc_skipping_static_element')
                    . ': ' . $element);
                    continue;
                }
                if (isset($fields['filename'])) {
                    $fileName = $fields['filename'];
                } else {
                    $fileName = $this->helpers->getFileName($element, $class);
                }
                $dir = $directory . $elementType . '/';
                if (file_exists($dir . $fileName)) {
                    $alias = $this->helpers->getNameAlias($class);
                    $object = $this->modx->getObject($class, array($alias => $element));
                    if ($object) {
                        /* check again in case config file is wrong */
                        $static = $object->get('static');
                        if (!empty($static)) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                $this->modx->lexicon('mc_skipping_static_element')
                                    . $element);
                            continue;
                        }
                        $content = file_get_contents($dir . $fileName);
                        if (!empty($content)) {
                            if ($dryRun) {
                                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
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
                                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                            '    ' .
                                        $this->modx->lexicon('mc_setting_properties_for')
                                        . ' ' . $element);
                                        $object->setProperties($props);
                                    }
                                }

                                if ($object->save()) {
                                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                        '    ' . $this->modx->lexicon('mc_updated')
                                        . ': ' . $element);
                                }
                            }
                        }
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                            $this->modx->lexicon('mc_element_nf')
                            . ': ' . $element);
                    }
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        $this->modx->lexicon('mc_file_nf')
                        . ': ' . $fileName);
                }
            }

        }

    }

    /* *****************************************************************************
        Development Utilities
     ***************************************************************************** */
    /**
     * Utility function to remove objects from MODX during development
     */
    public function removeObjects($removeFiles = false) {
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
            $this->modx->lexicon('mc_action')
            . ': ' .
            $this->modx->lexicon('mc_remove_objects'));
        $oldLogLevel = $this->modx->setLogLevel(MODX::LOG_LEVEL_ERROR);
        $this->createObjects(MODE_REMOVE);
        if ($removeFiles) {
            $dir = $this->targetRoot;
            if (! ($this->targetRoot == $this->props['targetRoot'])) {
                die('mismatched targetRoot -- aborting removeObjects');
            }
            $temp = $this->modx->setLogLevel(MODX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(MODx::LOG_LEVEL_INFO,
                $this->modx->lexicon('mc_removing_project_files'));
            $this->modx->setLogLevel($temp);
            $this->rrmdir($dir);
            if (! is_dir($dir)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_files_and_directories_removed'));
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                    $this->modx->lexicon('mc_vc_files_may_remain'));
            }
        }
        $this->modx->setLogLevel($oldLogLevel);
        $cm = $this->modx->getCacheManager();
        $cm->refresh();
    }

    /** recursive remove dir function */
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

