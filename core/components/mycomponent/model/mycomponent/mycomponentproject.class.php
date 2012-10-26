<?php

if (!defined('MODE_BOOTSTRAP')) {
    define('MODE_BOOTSTRAP', 0);
    define('MODE_EXPORT', 1);
    define('MODE_IMPORT', 2);
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

        $helpers = new Helpers($this->modx, $this->props);
        $this->helpers = $helpers;
        $this->helpers->init();

        $this->dirPermission = $this->props['dirPermission'];
        $this->updateProjectsFile($projectConfigPath);
        $this->configPath = $projectConfigPath;
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
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Updated projects.php file');
            }

        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Created projects.php file');
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject] MyComponent must be installed to create a new MyComponent Project!');
            return;
        }
        $mem_usage = memory_get_usage();
        echo "\nInitial Memory Use: " . round($mem_usage / 1048576, 2) . " megabytes";

        $modx = $this->modx;
        $helpers = $this->helpers;
        $objects = $this->bootstrapObjects;

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
     * For Bootstrap, creates objects in MODX and
     * creates code files for them
     *
     * For Export Objects, finds objects in MODX
     * and creates code files for them.
     *
     * In both cases the objects fields and resolver
     * fields are written to ObjectAdapter::myObjects
     *
     * @param int $mode
     */
    public function createObjects($mode = MODE_BOOTSTRAP) {
        /*  Create namespace */
        $this->createNamespaces($mode);

        /* create category or categories*/
        $this->createCategories($mode);

        /* create system settings */
        $this->createNewSystemSettings($mode);

        /* create new system events */
        $this->createNewSystemEvents($mode);

        /* Create elements */
        $this->createElements($mode);

        /* Create resources */
        $this->createResources($mode);

    }

    public function createNamespaces($mode = MODE_BOOTSTRAP) {
        if (!empty($this->props['namespaces'])) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating namespace(s)');
            foreach ($this->props['namespaces'] as $namespace => $fields) {
                if ($mode == MODE_BOOTSTRAP) {
                    $this->addToModx('NameSpaceAdapter', $fields);
                } elseif ($mode == MODE_EXPORT) {
                    new NamespaceAdapter($this->modx, $this->helpers, $fields);
                }

            }
        }
    }

    public function createCategories($mode = MODE_BOOTSTRAP) {
        $categories = $this->modx->getOption('categories', $this->props, array());

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
            $o = new CategoryAdapter($this->modx, $this->helpers, $fields, $mode);

            if ($mode == MODE_BOOTSTRAP) {
                $o->addToModx();
            } elseif ($mode == MODE_EXPORT) {
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
                foreach ($possibleElements as $element) {
                    if (in_array($element, $elementsToProcess)) {
                        $toProcess[] = $element;
                    }
                }
                unset($elementsToProcess, $possibleElements);
                $o->exportElements($toProcess, !empty($this->props['dryRun']));
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
        if ($mode == MODE_BOOTSTRAP) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating new System Settings');
            foreach ($this->props['newSystemSettings'] as $key => $fields) {
                if (!isset($fields['key'])) {
                    $fields['key'] = $key;
                }
                $this->addToModx('SystemSettingAdapter', $fields);
            }

        } elseif ($mode == MODE_EXPORT) {
            /* These still come from the project config file  */
            foreach ($newSystemSettings as $setting => $fields) {
                $obj = $this->modx->getObject('modSystemSetting', array('key' => $fields['key']));
                if ($obj) {
                    $fields = $obj->toArray();
                    new SystemSettingAdapter($this->modx, $this->helpers, $fields, $mode);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        '[MyComponentProject] Could not find System Setting with key: ' . $fields['key']);
                }
            }
        }
    }

    public function createNewSystemEvents($mode = MODE_BOOTSTRAP) {
        $newSystemEvents = $this->modx->getOption('newSystemEvents', $this->props, array());
        if (empty($newSystemEvents)) {
            return;
        }
        if ($mode == MODE_BOOTSTRAP) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating new System Events');
            foreach ($newSystemEvents as $key => $fields) {
                $fields['name'] = isset($fields['name'])
                    ? $fields['name']
                    : $key;

                $this->addToModx('SystemEventAdapter', $fields);
            }
        } elseif ($mode == MODE_EXPORT) {
            /* These come from the project config file */
            foreach ($newSystemEvents as $k => $fields) {
                $obj = $this->modx->getObject('modEvent', array('name' => $fields['name']));
                if ($obj) {
                    $fields = $obj->toArray();
                    new SystemEventAdapter($this->modx, $this->helpers, $fields);
                }
            }
        }

    }

    public function createElements($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP) {
            /* Create elements from the project config file.
             * In Export, they're pulled by category in the
             * CategoryAdapter, so not done here */
            if (isset($this->props['elements']) && !empty($this->props['elements'])) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating elements');
                $elements = $this->props['elements'];
                foreach ($elements as $element => $elementObjects) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating ' . $element);
                    foreach ($elementObjects as $elementName => $fields) {
                        /* @var $adapter elementAdapter */
                        $adapterName = ucFirst(substr($element, 0, -1)) . 'Adapter';
                        $fields['name'] = isset($fields['name'])
                            ? $fields['name']
                            : $elementName;

                        $o = $this->addToModx($adapterName, $fields);
                        $o->createCodeFile();
                    }
                }
            }
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject] createElements() called in Export mode');
        }
    }

    public function createResources($mode = MODE_BOOTSTRAP) {
        if ($mode == MODE_BOOTSTRAP) {
            if (isset($this->props['resources']) && !empty($this->props['resources'])) {
                /* @var $o ResourceAdapter */
                $o = null;
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Resources');
                foreach ($this->props['resources'] as $resource => $fields) {
                    $fields['pagetitle'] = empty($fields['pagetitle'])
                        ? $resource
                        : $fields['pagetitle'];

                    $o = $this->addToModx('ResourceAdapter', $fields);
                    $o->createCodeFile();
                }
            }
        } elseif ($mode == MODE_EXPORT) {
            /* Resource Adapter gets resources based on the project config file's
             * 'ExportResources' member */
            ResourceAdapter::exportResources($this->modx, $this->helpers, $this->props);
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
        /* @var $o ObjectAdapter */
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
        CategoryAdapter::createResolver($dir, $intersects, $this->helpers, $mode);

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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating resolver: ' . $name);
            $tpl = $this->helpers->getTpl('genericresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                    '[MyComponentProject] genericresolver tpl is empty');
                continue;
            }
            $fileName = $name . '.resolver.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' resolver already exists');
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating validator: ' . $name);
            $tpl = $this->helpers->getTpl('genericvalidator.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                    '[MyComponentProject] genericvalidator tpl is empty');
                continue;
            }
            $fileName = $name . '.validator.php';
            if (!file_exists($dir . '/' . $fileName)) {
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' validator already exists');
            }
        }
    }


    public function createTransportFiles($mode = MODE_BOOTSTRAP) {
        ElementAdapter::createTransportFiles($this->helpers, $mode);
        ResourceAdapter::createTransportFiles($this->helpers, $mode);
        SystemSettingAdapter::createTransportFiles($this->helpers, $mode);
        SystemEventAdapter::createTransportFiles($this->helpers, $mode);
    }

    /** Creates main build.transport.php, build.config.php and
     * starter project config files, (optionally) lexicon files, doc file,
     *  readme.md -- files only, creates no objects in the DB */
    public function createBasics() {

        /* Transfer build.transport.php and build.config.php files */

        $dir = $this->myPaths['targetBuild'];
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating build files');
        $fileName = 'build.transport.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl($fileName);
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
        }
        $fileName = 'build.config.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl('build.config.php');
            $tpl = str_replace('[[+packageNameLower]]', $this->packageNameLower, $tpl);
            $dir = $this->myPaths['targetBuild'];
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
        }
        $fileName = $this->packageNameLower . '.config.php';
        $dir = $this->myPaths['targetBuild'] . 'config/';
        if (!file_exists($dir . $fileName)) {
            $tpl = $this->helpers->getTpl('example.config.php');
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
        }

        /* Create language directories and files specified in project config */
        if (isset ($this->props['languages']) && !empty($this->props['languages'])) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Lexicon files');
            $lexiconBase = $this->myPaths['targetCore'] . 'lexicon/';
            foreach ($this->props['languages'] as $language => $languageFiles) {
                $dir = $lexiconBase . 'lexicon/' . $language;
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
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $language . '/' . $fileName . ' file already exists');
                    }

                }
            }
        }

        $docs = isset($this->props['docs'])
            ? $this->props['docs']
            : array();

        if (!empty($docs)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating doc files');
            $toDir = $this->myPaths['targetCore'] . 'docs/';
            foreach ($docs as $doc) {
                if (!file_exists($toDir . $doc)) {
                    $tpl = $this->helpers->getTpl($doc);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($toDir, $doc, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $doc . ' file already exists');
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
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    readme.md file already exists');
            }

        }

        $fileContent = file_get_contents($this->mcRoot . '_build/utilities/jsmin.class.php');
        if (!empty($fileContent)) {
            if (!file_exists($this->myPaths['targetBuild'] . 'utilities/jsmin.class.php')) {
                $this->helpers->writeFile($this->myPaths['targetBuild'] . 'utilities', 'jsmin.class.php', $fileContent);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    jsmin class file already exists');
            }
        }

        $this->createInstallOptions();
        $this->createAssetsDirs();
        $this->createClassFiles();

        return true;
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
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Assets directories');
        foreach ($optionalDirs as $dir => $val) {
            $targetDir = $this->myPaths['targetAssets'] . $dir;
            if ($val && (!is_dir($targetDir))) {
                if (mkdir($targetDir, $this->dirPermission, true)) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Created ' . $targetDir . ' directory');
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Assets/' . $dir . ' directory already exists');
            }
            if ($dir == 'css' || $dir == 'js') {
                $path = $this->myPaths['targetAssets'] . $dir;
                $fileName = $this->packageNameLower . '.' . $dir;
                if (!file_exists($path . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl($dir);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($path, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '        ' . $fileName . ' file already exists');
                }

            }
        }
    }


    /** Creates example file for user input during install if set in project config file */
    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (!empty($iScript)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Install Options');
            $dir = $this->targetRoot . '_build/install.options';
            $fileName = 'user.input.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }

    /** Creates validators if set in project config file */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating validators');
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
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating class files');
            $baseDir = $this->myPaths['targetCore'] . 'model';
            foreach ($classes as $className => $data) {
                $data = explode(':', $data);
                if (!empty($data[1])) {
                    $dir = $baseDir . '/' . $data[0] . '/' . $data[1];
                } else { /* no directory */
                    $dir = $baseDir . '/' . $data[0];
                }
                $fileName = strtolower($className) . '.class.php';
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl);
                    $tpl = str_replace('[[+className]]', $className, $tpl);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
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
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject] MyComponent must be installed to export the Project from MODx!');
            return;
        }

        $mode = MODE_EXPORT;

        $toProcess = $this->modx->getOption('process', $this->props, array());
        $this->createNamespaces($mode);

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
    }


    /**
     * Utility function to remove objects from MODX during development
     */
    public function removeObjects() {
        $categories = $this->props['categories'];
        foreach ($categories as $category => $fields) {
            $catObj = $this->modx->getObject('modCategory', array('category' => $category));
            if ($catObj) {
                $categoryId = $catObj->get('id');
            } else {
                die('could not find category: ' . $category);
            }
            $elements = array(
                'modChunk',
                'modSnippet',
                'modTemplate',
                'modTemplateVar',
                'modPlugin',
                'modPropertySet',
            );

            foreach ($elements as $element) {

                $objs = $this->modx->getCollection($element, array('category' => $categoryId));
                foreach ($objs as $obj) {
                    /* @var $obj xPDOObject */
                    if ($obj) {
                        $obj->remove();
                    }
                }
            }
            $catObj->remove();
        }


        $objects = array(
            'Resource1' => 'modResource',
            'Resource2' => 'modResource',
            'Snippet1' => 'modSnippet',
            'Snippet2' => 'modSnippet',
            'Chunk1' => 'modChunk',
            'Chunk2' => 'modChunk',
            'Plugin1' => 'modPlugin',
            'Plugin2' => 'modPlugin',
            'Template1' => 'modTemplate',
            'Template2' => 'modTemplate',
            'Tv1' => 'modTemplateVar',
            'Tv2' => 'modTemplateVar',
            'Example' => 'modNamespace',
            'OnMyEvent1' => 'modEvent',
            'OnMyEvent2' => 'modEvent',
            'PropertySet1' => 'modPropertySet',
            'PropertySet2' => 'modPropertySet',
        );

        foreach ($objects as $object => $type) {
            $name = 'name';
            if ($type == 'modTemplate') $name = 'templatename';
            if ($type == 'modResource') $name = 'pagetitle';
            if ($type == 'modSystemSetting') $name = 'key';
            $obj = $this->modx->getObject($type, array($name => $object));
            if (!$obj) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '[MyComponentProject] Could not find ' . $type . ' ' . $object);
            } else {
                $obj->remove();
            }
            $c = $this->modx->getObject('modCategory', array('category' => 'Example'));
            if ($c) {
                $c->remove();
            }
        }
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
    public function importObjects($toProcess, $directory, $dryRun = true) {
        $toProcess = explode(',', $toProcess);
        foreach ($toProcess as $elementType) {
            $class = 'mod' . ucfirst(substr($elementType, 0, -1));
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Processing ' . $elementType);
            $elements = $this->props['elements'][$elementType];
            foreach ($elements as $element => $fields) {
                if (isset($fields['static']) && !empty($fields['static'])) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                        '    Skipping static element: ' . $element);
                    continue;
                }
                if (isset($fields['filename'])) {
                    $fileName = $fields['filename'];
                } else {
                    $fileName = $this->helpers->getFileName($element, $class);
                }
                if (file_exists($fileName)) {
                    $alias = $this->helpers->getNameAlias($class);
                    $object = $this->modx->getObject($class, array($alias => $element));
                    if ($object) {
                        /* check again in case config file is wrong */
                        $static = $object->get('static');
                        if (!empty($static)) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                '    Skipping static element: ' . $element);
                            continue;
                        }
                        $content = file_get_contents($fileName);
                        if (!empty($content)) {
                            if ($dryRun) {
                                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                    '    Would be updating: ' . $element);
                            } else {
                                $object->setContent($content);
                                if ($object->save()) {
                                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,
                                        '    Updated: ' . $element);
                                }
                            }
                        }
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                            'Could not find element: ' . $element);
                    }
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR,
                        'Could not find file: ' . $fileName);
                }
            }

        }

    }
}

