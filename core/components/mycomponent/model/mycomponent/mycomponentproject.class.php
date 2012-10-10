<?php

class MyComponentProject {
    /* @var $modx modX */
    public $modx;

    public $myName;
    public $myVersion;

// List of Paths for MC and the Project
    public $myPaths = array();
    public $packageNameLower = '';
// List of all Objects
    //public $myObjects = array();
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


/* *****************************************************************************
   Property Getter and Setters
***************************************************************************** */
    /**
     * Convenience method for determining if MyComponent is installed.
     *
     * @return boolean - True, if MC is installed. False, if not.
     */
    public function isMCInstalled()
    {//Simple Getter
        return $this->myPaths['mcCore'] != '';
    }

/* *****************************************************************************
   Construction and Support Functions (in MODxObjectAdapter)
***************************************************************************** */
    public function __construct()
    {
        /* Create $modx object if it doesn't exist */
        $this->initMODx();
        /* Get the config file */
        $this->init();
        /* Set up our paths */
        $this->initPaths();

        // $output =  print_r($this->props,true);
        // echo $output;
    }

    /* Instantiate MODx -- if this require fails, check your
     * _build/build.config.php file
     */
    public function initMODx() { /* Initialize MODX if not already done */
        if (!defined('MODX_CORE_PATH')){

            require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) .'/_build/build.config.php';
            $cp = MODX_CORE_PATH;

            if (empty($cp)) {
                die ('Could not initialize MODX');

            }
            require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

            $modx= new modX();
        }
        $this->modx =& $modx;
        /* Initialize and set up logging */
        $modx->initialize('mgr');
        $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
        $modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

        /* This section will only run when operating outside of MODX */
        if (php_sapi_name() == 'cli') {
            /* @var $loginFile string - set in build.config.php */
            /* @var $fields string - set in login file at the path $loginFile */
            include $loginFile;
            $modx->getRequest();
            /* Log in */
            $response = $modx->runProcessor('security/login', $fields);
            /* Check login success */
            if ($response->isError()) {
                if ($response->hasFieldErrors()) {
                    $fieldErrors = $response->getAllErrors();
                    $errorMessage = implode("\n", $fieldErrors);
                } else {
                    $errorMessage = 'An error occurred: ' . $response->getMessage();
                }
                die($errorMessage);
            }

            /* Set $modx->resource and $modx->user */
            $user = $modx->getObject('modUser', array('username' => $fields['username']));
            if ($user instanceof modUser) {
                $modx->user =& $user;
            } else {
                echo "\nNo User\n";
            }
            $homeId = $modx->getOption('site_start');
            $homeResource = $modx->getObject('modResource', $homeId);

            if ($homeResource instanceof modResource) {
                $modx->resource &= $homeResource;
            } else {
                echo "\nNo Resource";
            }
            /* One last check */
            if (! $modx->user->hasSessionContext('mgr')) {
                die ('User is not logged in');
            }
        }
    }

    public function init() {
        require dirname(__FILE__) . '/mcautoload.php';
        spl_autoload_register('mc_auto_load');
        // Get the project config file
        include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/config/current.project.php';
        if (! isset($configPath)) {
            die('Config path not set');
        }
        if (file_exists($configPath)) {
            $properties = @include $configPath;
        } else {
            die('Could not find project config file at ' . $configPath);
        }
        // Make sure that we get usable values
        if (empty($properties)) {
            die('Config File was not set up correctly: ' . $configPath);
        }

        $this->packageNameLower = $properties['packageNameLower'];

        $this->mcRoot = isset($properties['mycomponentRoot'])
            ? $properties['mycomponentRoot']
            : '';
        if ( empty($this->mcRoot)) {
            die('mycomponentRoot is not set');
        }
        $this->targetRoot = isset($properties['targetRoot'])
            ? $properties['targetRoot']
            : '';

        if (empty($this->targetRoot)) {
            die('targetRoot is not set');
        }
        $this->props = $properties;
        $helpers = new Helpers($this->modx, $this->props);
        $this->helpers = $helpers;
        $this->helpers->init();

        $this->dirPermission = $this->props['dirPermission'];

        $this->bootstrapObjects = $this->getBootstrapObjects();

        /* Create or update projects.php file */
        $this->updateProjectsFile($configPath);


    }

    public function updateProjectsFile($configPath) {
        $projectsFile = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/_build/config/projects.php';
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
                $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Updated projects.php file');
            }

        } else {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Created projects.php file');
            $content = $header . "    '" . $newContent . $footer;
            $fp = fopen($projectsFile, 'w');
            fwrite($fp, $content);
            fclose($fp);

        }
    }

    /* Creates an array of object names and fields from the project config file */
    public function getBootstrapObjects() {
        $config = $this->props;
        $objects = array();

        /* get Categories for quick create */
        if (isset($config['categoryNames'])) {
            foreach($config['categoryNames'] as $category => $fields) {
                if (!isset($fields['category'])) {
                    $fields['category'] = $category;
                }
                $objects['categoryNames'][$category] = $fields;
            }
        }

        /* get namespace(s) */
        if (isset($config['namespaces']) && !empty($config['namespaces'])) {
            foreach ($config['namespaces'] as $settingName => $settingFields) {
                if (!isset($fields['name'])) {
                    $fields['name'] = $settingName;
                }
                $objects['namespaces'][$settingName] = $settingFields;
            }
        }

        /* get new System Settings */
        if (isset($config['newSystemSettings']) && ! empty($config['newSystemSettings'])) {
            foreach($config['newSystemSettings'] as $settingName => $settingFields) {
                $objects['newSystemSettings'][$settingName] = $settingFields;
                if (!isset($fields['key'])) {
                    $fields['key'] = $settingName;
                }
                /* add namespaces set as field to namespaces member */
                if (isset($settingFields['namespace'])) {
                    $objects['namespaces'][$settingFields['namespace']]['name'] = $settingFields['namespace'];
                }
            }
        }

        /* get new System Events */
        if (isset($config['newSystemEvents']) && !empty($config['newSystemEvents'])) {
            foreach ($config['newSystemEvents'] as $settingName => $settingFields) {
                $settingFields = is_array($settingFields)
                    ? $settingFields
                    : array();
                $objects['newSystemEvents'][$settingName] = $settingFields;
            }
        }

        /* get PropertySets */
        if (isset($config['propertySets']) && !empty($config['propertySets'])) {
            foreach ($config['propertySets'] as $settingName => $settingFields) {
                $objects['propertySets'][$settingName] = $settingFields;
            }
        }

        /* get elements -- all are placed in their own category */
        $elementList = isset($config['elements'])
            ? $config['elements']
            : array();
        if (!empty($elementList)) {
            foreach ($elementList as $type => $elements) {
                foreach ($elements as $element => $fields) {
                    if (! isset($fields['category'])) {
                        die("\n\nERROR -- All Elements must have a category");
                    }
                    $category = $fields['category'];
                    if (! in_array($fields['category'], array_keys($objects['categoryNames']))) {
                        $objects['categoryNames'][$fields['category']]['category'] = $fields['category'];
                    }

                    /* normalize name field for templates*/
                    if ($type == 'templates') {
                        if (!isset($fields['templatename'])) {
                            $fields['templatename'] = isset($fields['name'])
                                ? $fields['name']
                                : $element;
                        }
                        unset($fields['name']);
                    } else {
                        $fields['name'] = isset($fields['name'])
                            ? $fields['name']
                            : $element;
                    }

                    if (isset($config['allStatic']) && !empty($config['allStatic'])) {
                        $fields['static'] = true;
                    } else {
                        if (isset($fields['static'])) {
                            $fields['static'] = (bool) !empty($fields['static']);
                        }
                    }

                    if ($type == 'plugins' || isset($fields['events'])) {
                        if (is_array($fields['events'])) {
                            foreach($fields['events'] as $event => $eventFields) {
                                $tempFields['plugin'] = $element;
                                $tempFields['event'] = $event;
                                $eventFields = $tempFields + $eventFields;
                                unset($eventFields['group']);
                                $objects['pluginEvents'][] = $eventFields;
                            }
                        }
                    }
                    if (isset($fields['propertySets']) && !empty($fields['propertySets'])) {
                        foreach($fields['propertySets'] as $k => $setName) {
                            $objects['elementPropertySets'][] = array(
                                'element' => $element,
                                'element_class' => 'mod' . ucfirst(substr($type, 0, -1)),
                                'property_set' => $setName,

                            );
                        }
                    }

                    if ($type == 'templateVars' && !empty($fields['templates'])) {
                        foreach($fields['templates'] as $templateName => $rank) {
                            if (is_numeric($templateName)) {
                                /* user left out rank */
                                $templateName = $rank;
                                $rank = 0;
                            }
                            if ($templateName == 'default') {
                                $t = $this->modx->getOption('default_template');
                                $template = $this->modx->getObject('modTemplate', $t);
                                $templateName = $template->get('templatename');
                            }

                            //$objects['templateVarTemplates'][$element][$templateName] = $rank;
                            $objects['templateVarTemplates'][] = array(
                                'tmplvarid' => $element,
                                'templateid' => $templateName,
                                'rank' => $rank,
                            );
                        }
                    }

                    $objects['categories'][$category][$type][$element] = $fields;
                }
            }
        }

        /* get resources */
        $resources = isset($config['resources'])
            ? $config['resources']
            : array();

        if (!empty($resources)) {
            $this->hasResources = true;
            foreach($resources as $resource => $fields) {

                $fields['pagetitle'] = isset($fields['pagetitle'])
                    ? $fields['pagetitle']
                    : $resource;
                $objects['resources'][$resource] = $fields;
                if (isset($fields['parent'])) {
                    $objects['resourceParents'][$fields['pagetitle']] = $fields['parent'];
                }
                if (isset($fields['template'])) {
                    $objects['resourceTemplates'][$fields['pagetitle']] = $fields['template'];
                }
                if (isset($fields['TvValues'])) {
                    if (is_array($fields['TvValues'])) {
                        foreach ($fields['TvValues'] as $k => $v) {
                            $objects['resourceTvs'][$k] = $v;
                        }
                    }
                }
            }
        }

        // die(print_r($objects, true));
        //$this->bootstrapObjects = $objects;
        return $objects;
    }


    /* Creates an array just like the one in the previous method, but from the MODX
     * db based on criteria set by the ExportObjects section of the project config file. */
    public function getExportObjects() {
        $objects = array();


        $this->exportObjects = $objects;
        return $objects;

    }

    /**
     * Sets up the Path variables for the Component Project. Runs automatically
     * on __construct.
     */
public function initPaths() {

    $paths = array();

    $name = $this->props['packageNameLower'];
    // @var $ns modNameSpace
    // $ns = null;
    //$ns = $this->modx->getObject('modNamespace', array('key' => 'mycomponent'));
    $paths['mcRoot'] = $this ->mcRoot;
    $paths['mcCore'] = $this->mcRoot . 'core/components/mycomponent/';
    $paths['mcModel'] = $paths['mcCore'] . 'model/mycomponent/';
    $paths['mcBuild'] = $this->mcRoot . '_build/';
    $paths['mcElements'] = $paths['mcCore'] . 'elements/';
    $paths['mcTpl'] = $paths['mcElements'] . 'chunks/';

    /*  Set the Root path for this Component */
    $paths['targetRoot'] = $this->targetRoot;
    /* Basic Paths */
        $paths['targetCore']      = $paths['targetRoot'] . 'core/components/' . $name . '/';
            $paths['targetControl']   = $paths['targetCore'] . 'controllers/';
            $paths['targetDocs']      = $paths['targetCore'] . 'docs/';
            $paths['targetElements']  = $paths['targetCore'] . 'elements/';
            $paths['targetLexicon']   = $paths['targetCore'] . 'lexicon/';
            $paths['targetModel']     = $paths['targetCore'] . 'model/' . $name . '/';
            $paths['targetProcess']   = $paths['targetCore'] . 'processors/';
        $paths['targetAssets']    = $paths['targetCore'] . 'assets/components/' . $name . '/';
            $paths['targetCss']       = $paths['targetAssets'] . 'css/';
            $paths['targetJs']        = $paths['targetAssets'] . 'js/';
            $paths['targetImages']    = $paths['targetAssets'] . 'images/';
        $paths['targetBuild']     = $paths['targetRoot'] . '_build/';
            $paths['targetData']      = $paths['targetBuild'] . 'data/';
            $paths['targetProperties'] = $paths['targetData'] . 'properties/';
            $paths['targetResolve']   = $paths['targetBuild'] . 'resolvers/';
            $paths['targetValidate']  = $paths['targetBuild'] . 'validators/';
    /* Set to $myPathcs Class Member */
        $this->myPaths = $paths;

    /* dump object array to file for reference */
    $objectArray = print_r($this->bootstrapObjects, true);
    $this->helpers->writeFile($paths['mcBuild'], 'objectarray.txt', $objectArray);
    //$fp = fopen($this->myPaths['mcBuild'] . 'objectarray.txt', "w");
    /*if ($fp) {
        fwrite($fp, $objectArray);
        fclose($fp);
        $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Created ' . $this->myPaths['mcBuild'] . 'objectarray.txt');
    }
    else {
        $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Could not open ' . $this->myPaths['mcBuild'] . 'objectarray.txt');
    }*/

}

    /* Not used */
    public function loadChildren()
    {   if ($this->runBuild)
        {//For quick access
            $data = $this->getPath('data');
        // Load Resources
            $resources = include $data.'transport.resources.php';
            foreach ($resources as $resource)
                $this->myResources[] = new ComponentResource($this, $resource);

        // Load System Settings
            $settings = include $data.'transport.settings.php';
            foreach ($settings as $setting)
                $this->mySettings[] = new ComponentSetting($this, $setting);

        // Load the Action Menu
            $menu = include $data.'transport.menu.php';
            $this->myMenu = new ComponentMenu($this, $menu);
        }

        if ($this->copyCore
        &&  $this->runBuild)
        {

        }
    }

/* *****************************************************************************
   Property Getter and Setters
***************************************************************************** */

    /**
     * Convenience Method for getting the File System Safe Name of the component.
     *
     * @return string - The lowercase Name of the component with no spaces.
     */
    public function getSafeName()
    {//Simple Getter Function
        return str_replace(' ', '', strtolower($this->name));
    }

    /**
     * Generic Getter for the path of the desired key for the Project. This enforces
     * consistency and reduces the need for path building in other objects.
     *
     * @param $target string - The key to retrieve the path for.
     * @return string - The path requested.
     */
    public function getPath($target)
    {//We already have this stored!!
        return $this->myPaths[$target];
    }

    /**
     * Deprecated: See getPath('code') and ElementAdapter->getCodeDir.
     */
    public function getCodeDir ($targetCore, $type) {    }


/* *****************************************************************************
   Bootstrap and Support Functions
***************************************************************************** */

    public function bootstrap() {
        /* enable garbage collection() */
        // gc_enable();
        if (!$this->isMCInstalled()) /* Only run if MC is installed */
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to create a new MyComponent Project!');
            return;
        }
echo "\n" . memory_get_usage();
        $modx = $this->modx;
        $helpers = $this->helpers;
        $objects = $this->bootstrapObjects;

    /* Create basic files (no transport files or code files) */
    $this->createBasics();

    /*  Create namespace */
        if (!empty($objects['namespaces'])) {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Creating namespace(s)');
            foreach ($objects['namespaces'] as $namespace => $fields) {
                $this->addToModx('NameSpaceAdapter', $fields);
                /*$n = new NamespaceAdapter($modx, $helpers, $fields);
                $n ->addToModx();*/
            }
       }
echo "\n" . memory_get_usage();

    /* quick create category or categories*/
        if (!empty($objects['categoryNames'])) {
            foreach($objects['categoryNames'] as $categoryName => $fields) {
                if (empty($fields['category'])) {
                    $fields['category'] = $categoryName;
                }
                $o = new CategoryAdapter($modx, $helpers, $fields);
                $o->addToModx();
            }
        }
echo "\n" . memory_get_usage();

    /* create system settings */
        if (!empty($objects['newSystemSettings'])) {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Creating new System Settings');
            foreach($objects['newSystemSettings'] as $key => $fields) {
                $this->addToModx('SystemSettingAdapter', $fields);
                /*$r = new SystemSettingAdapter($modx, $helpers, $fields);
                $r->addToModx();*/
            }
        }
echo "\n" . memory_get_usage();
    /* create new system events */
        if (!empty($objects['newSystemEvents'])) {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Creating new System Events');
            foreach($objects['newSystemEvents'] as $key => $fields) {
                $fields['name'] = $key;
                $this->addToModx('SystemEventAdapter', $fields);
                /*$r = new SystemEventAdapter($modx, $helpers, $fields);
                $r->addToMODx();*/
            }
        }

    /* Create category and all its elements */

        if (isset($objects['categories']) && !empty($objects['categories'])) {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Creating category elements');
            $categories = $objects['categories'];
            foreach($categories as $category => $fields) {
                $fields['category'] = $category;
                /* @var $catAdapter CategoryAdapter */
                $catAdapter = $this->addToModx('CategoryAdapter', $fields);
                /* second argument says to create code files too */
                $catAdapter->addChildren($fields, true);
            }
        }

echo "\n" . memory_get_usage();
    /* Create resources */
        if (isset($objects['resources']) && !empty($objects['resources'])) {
            $this->helpers->sendLog(MODX_LOG_LEVEL_INFO, 'Creating Resources');
            foreach($objects['resources'] as $resource => $fields) {
                $fields['pagetitle'] = empty($fields['pagetitle'])
                    ? $resource
                    : $fields['pagetitle'];

                $this->addToModx('ResourceAdapter', $fields);

                /*$r = new ResourceAdapter($modx, $helpers, $fields);
                $r->addToMODx();*/
            }
        }

        /* Create intersects for many-to-many objects */
        $this->connectTvsToTemplates();
        $this->connectPluginsToEvents();
        $this->connectElementsToPropertySets();

$mem_usage = memory_get_usage();
echo "\n" . round($mem_usage / 1048576, 2) . " megabytes";

    }
    /* add to MODx function -- separating this allows
     * more frequent garbage collection */
    protected function addToModx($adapter, $fields, $overwrite = false) {
        /* @var $o ObjectAdapter */
        $o = new $adapter($this->modx, $this->helpers, $fields);
        $o->addToMODx();
        return $o;

    }

    public function connectTvsToTemplates() {
        $templateVarTemplates = $this->bootstrapObjects['templateVarTemplates'];
        $this->helpers->createIntersects('modTemplateVarTemplate', $templateVarTemplates);
    }
    public function connectPluginsToEvents() {
        $pluginEvents = $this->bootstrapObjects['pluginEvents'];
        $this->helpers->createIntersects('modPluginEvent', $pluginEvents);
    }
    public function connectElementsToPropertySets() {
        $propertySets = $this->bootstrapObjects['elementPropertySets'];

        if (!empty($propertySets)) {
            $this->helpers->createIntersects('modElementPropertySet', $propertySets);
        }
    }

    public function newPaths()
    {//For Quick Access
        $paths = $this->paths;
        $needs = $this->hasObjects;

    // Iterate through array
        foreach($needs as $key => $value)
        {   if ($value == true)
                $this->makeDir($paths[$key]);
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Created Directory: ' . $paths['key']);
        }
    }

    /** Initializes class variables  NOT USED */
    public function xinit($configPath) {
        clearstatcache(); /*  make sure is_dir() is current */
        $config = $configPath;
        if (file_exists($config)) {
            $configProps = @include $config;
        } else {
            die('Could not find main config file at ' . $config);
        }

        if (empty($configProps)) {
            /* @var $configFile string - defined in included build.config.php */
            die('Could not find project config file at ' . $configFile);
        }
        $this->props = array_merge($configProps, $this->props);
        unset($config, $configFile, $configProps);

        $this->source = $this->props['source'];
        /* add trailing slash if missing */
        if (substr($this->source, -1) != "/") {
            $this->source .= "/";
        }
        require_once $this->source . 'core/components/mycomponent/model/mycomponent/helpers.class.php';
        $this->helpers = new Helpers($this->modx, $this->props);
        $this->initHelpers();

        $this->packageName = $this->props['packageName'];
        $this->packageNameLower = $this->props['packageNameLower'];


        if (isset($this->props['offerAbort']) && $this->props['offerAbort']) {
            echo 'Processing ' . $this->packageName . 'Continue? (y/n - Enter) ';
            $input = fgetc(STDIN);
            if ($input != 'y' && $input != 'Y') {
                die ('Operation aborted');
            }
        }

        $this->targetBase = MODX_BASE_PATH . 'assets/mycomponents/' . $this->packageNameLower . '/';
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';
        $this->targetAssets = $this->targetBase . 'assets/components/'. $this->packageNameLower . '/';

        $this->dirPermission = $this->props['dirPermission'];
        $this->filePermission = $this->props['filePermission'];

        $this->makeStatic = !empty($this->props['makeStatic'])? explode(',', $this->props['makeStatic']) : array();

        /* show basic info */
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Component: ' . $this->props['packageName']);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Source: ' . $this->source);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Target Base: ' . $this->targetBase);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Target Core: ' . $this->targetCore);
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Target Assets: ' . $this->targetAssets);

        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '--------------------------------------------------');
    }

    /** Creates main build.transport.php, build.config.php and
     * starter project config files, (optionally) lexicon files, doc file,
     *  readme.md -- files only, creates no objects in the DB */
    public function createBasics() {

        /* Transfer build and build config files */

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
            //copy($this->mcRoot . '_build/build.config.php', $dir . '/' . 'build.config.php');
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

        if (isset ($this->props['languages']) &&  ! empty($this->props['languages'])) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Creating Lexicon files');
            $lexiconBase = $this->myPaths['targetCore'] . 'lexicon/';
            foreach($this->props['languages'] as $language => $languageFiles) {
                $dir = $lexiconBase . 'lexicon/' . $language;
                $files = !empty($languageFiles)? $languageFiles : array();
                foreach($files as $file){
                    $fileName = $file . '.inc.php';
                    if (! file_exists($dir . '/' . $fileName)){
                        $tpl = $this->helpers->getTpl('phpfile.php');
                        $tpl = str_replace('[[+elementName]]', $language . ' '. $file . ' topic', $tpl);
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

        if (! empty($docs)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Creating doc files');
            $toDir = $this->myPaths['targetCore'] . 'docs/';
            foreach($docs as $doc) {
                if (! file_exists($toDir . $doc )) {
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
            if (! file_exists($this->myPaths['targetRoot'] . 'readme.md')) {
                $tpl = $this->helpers->getTpl('readme.md');
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($this->myPaths['targetRoot'], 'readme.md', $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    readme.md file already exists');
            }

        }
        $this->newInstallOptions();
        $this->newAssetsDirs();
        $this->createClassFiles();

        return true;
    }

    /** Creates assets directories and (optionally) empty css and js files
     * if set in project config file */
    public function newAssetsDirs() {
        if (! $this->props['hasAssets']) {
            return;
        }
        $optionalDirs = !empty($this->props['assetsDirs'])? $this->props['assetsDirs'] : array();
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Creating Assets directories');
        foreach($optionalDirs as $dir => $val) {
            $targetDir = $this->myPaths['targetAssets'] . $dir;
            if ($val && (! is_dir($targetDir)) ) {
                if (mkdir($targetDir, $this->dirPermission, true)) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'    Created ' . $targetDir . ' directory');
                }
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'    Assets/' . $dir . ' directory already exists');
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
    public function newInstallOptions()
    {//For Quick Access
        $path = $this->myPaths['targetBuild'] . 'install.options/';
        $fileName = 'user.input.php';

        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (! empty($iScript)) {

        // Check if the file exists
            if (file_exists($path . $fileName))
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Install Options file already exists');
            else
            {//Get the Templatized Basic file
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($path, $fileName, $tpl);
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Created Install Options at: ' . $path . $fileName);
            }
        }
    }



    /** Creates example file for user input during install if set in project config file */
    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (! empty($iScript)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Install Options');
            $dir = $this->targetRoot . '_build/install.options';
            $fileName = 'user.input.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }


    /** Creates propertyset objects in MODX install if set in project config file.
     * Create the property set's properties in the Manager and export them
     * with exportObjects */

    public function createPropertySets() {
        $propertySets = $this->modx->getOption('propertySets', $this->props, '' );
        if (! empty($propertySets)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating property sets');
            $propertySets = explode(',', $propertySets);
            foreach($propertySets as $name) {
                /* @var $set modPropertySet */
                $set = $this->modx->getObject('modPropertySet', array('name' => $name));
                if (! $set){
                    $fields = array(
                        'name' => $name,
                        'category' => $this->categoryId,
                    );
                    /* @var $setObj modPropertySet */
                    $setObj = $this->modx->newObject('modPropertySet', $fields);
                    if ($setObj && $setObj->save()) {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Created ' . $name . ' property set object');
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '    Could not create ' . $name . ' property set object');
                    }

                } else {
                    /* do this in case the set is leftover from a bad install
                     * and has the wrong category ID (won't show in tree). */
                    if ($set->get('category') != $this->categoryId) {
                        $set->set('category', $this->categoryId);
                        $set->save();
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Updated ' . $name . ' property set category');
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' property set already exists');
                    }
                }
            }
        }
    }

    /** Creates validators if set in project config file */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating validators');
            $dir = $this->targetRoot . '_build/validators';

            $validators = explode(',', $validators);
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
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    '  . $fileName . ' already exists');
                }
            }
        }
    }
    /** Creates additional resolvers specified in project config file */
    public function createExtraResolvers() {
        $resolvers = $this->modx->getOption('resolvers', $this->props, '');
        if (!empty($resolvers)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating extra resolvers');
            $dir = $this->myPaths['build'] . 'resolvers';
            if (!is_dir($dir)) {
                mkdir($dir, $this->dirPermission, true);
            }
            $resolvers = explode(',', $resolvers);
            foreach ($resolvers as $resolver) {
                if ($resolver == 'default') {
                    $fileName = $this->packageNameLower . '.' . 'resolver.php';
                } else {
                    $fileName = $resolver . '.' . 'resolver.php';
                }
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('genericresolver.php');
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }
            }
        }
    }

    /**
     * Connects Property Sets to Elements and creates a resolver to connect them
     * during the install.
     */
    public function createElementPropertySetResolver() {

        /* Create Resolver */
        if (!empty($propertySets)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating tv resolver');
            $tpl = $this->helpers->getTpl('propertysetresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'propertysetresolver tpl is empty');
            }
            $dir = $this->targetRoot . '_build/resolvers';
            $fileName = 'propertyset.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->helpers->getTpl('propertysetresolvercode.php');
                $codeTpl = str_replace('<?php', '', $codeTpl);

                foreach ($propertySets as $propertySet => $elements) {
                    $tempCodeTpl = str_replace('[[+propertySet]]', $propertySet, $codeTpl);
                    $tempCodeTpl = str_replace('[[+elements]]', $elements, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }

    }
    /** Creates "starter" class files specified in project config file */
    public function createClassFiles() {
        /* @var $element modElement */
        $classes = $this->modx->getOption('classes', $this->props, array());
        $classes = !empty($classes) ? $classes : array();
        if (!empty($classes)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating class files');
            $baseDir = $this->myPaths['targetCore'] . 'model';
            foreach($classes as $className => $data) {
                $data = explode(':', $data);
                if (!empty($data[1])) {
                    $dir = $baseDir . '/' . $data[0] . '/' . $data[1];
                } else {  /* no directory */
                    $dir = $baseDir . '/' . $data[0];
                }
                $fileName = strtolower($className) . '.class.php';
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl );
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
   Import Objects and Support Functions
***************************************************************************** */

    public function importComponent($overwrite = false)
    {//Only run if MC is installed
        if (!$this->isMCInstalled())
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to import the Project into MODx!');
            return;
        }

    // Crawl through the list top-down
        $objects = $this->myObjects;
        foreach ($objects as $child)
            if (!empty($child))
            // Each Child adds their own Children.
                $child->addToMODx();

    /* NOTE: This is where specific Intersects could be handled (if not handled by the objects themselves.) */
    }

/* *****************************************************************************
   Export Objects and Support Functions
***************************************************************************** */

    /**
     * This function does the real work of getting the package objects from the
     * MODx database.
     */
    public function exportComponent($overwrite = false)
    {//Only run if MC is installed
        if (!$this->isMCInstalled())
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to export the Project from MODx!');
            return;
        }

    // Crawl through the list top-down
        $objects = $this->myObjects;
        foreach ($objects as $child)
            if (!empty($child))
                $child->seekChildren();

    // Once we have the hierarchy, export everything...
        $objects = $this->myObjects;
        foreach ($objects as $child)
            if (!empty($child))
                $child->exportObject($overwrite);

    // Report Completion
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Finished');
    }

/* *****************************************************************************
   Build Vehicle and Support Functions
***************************************************************************** */

    /**
     * Builds the transport file from the folder hierarchy of the package.
     */
    public function buildComponent()
    {//Begin tracking the time to build
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $tstart = $mtime;
        set_time_limit(0);

    // Create the Namespace Object

        /* define sources */
        $root = dirname(dirname(__FILE__)) . '/';
        $sources= array (
            'root' => $root,
            'build' => $root . '_build/',
            /* note that the next two must not have a trailing slash */
            'source_core' => $root.'core/components/'.PKG_NAME_LOWER,
            'source_assets' => $root.'assets/components/'.PKG_NAME_LOWER,
            'resolvers' => $root . '_build/resolvers/',
            'validators'=> $root . '_build/validators/',
            'data' => $root . '_build/data/',
            'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
            'install_options' => $root . '_build/install.options/',
            'packages'=> $root . 'core/packages',
        );
        unset($root);

        /* load builder */
        $this->modx->loadClass('transport.modPackageBuilder','',false, true);


        $builder = new modPackageBuilder($this->modx);
        $builder->createPackage($this->myName, $this->myVersion, PKG_RELEASE);

    // Package Namespace
        $this->myNamespace->buildVehicle($builder);

    // Build each Resource
        if (!empty($this->myResources)
        &&  is_array($this->myResources))
            foreach ($this->myResources as $resource)
                if ($resource instanceof ComponentResource)
                    $resource->buildVehicle($builder);
    // Clear some Memory
        unset($this->myResources, $resource);


        /* minify JS */

        if ($this->minify)  {   $this->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');
            // require $sources['build'] . 'utilities/jsmin.class.php';
            require MYCOMPONENT_ROOT . 'core/components/mycomponent/model/mycomponent/jsmin.class.php';

            $jsDir = $sources['source_assets'] . '/js';

            if (is_dir($jsDir)) {
                $files = scandir($jsDir);
                foreach ($files as $file) {
                    /* skip non-js and already minified files */
                    if ( (!stristr($file, '.js') || strstr($file,'min'))) {
                        continue;
                    }

                    $jsmin = JSMin::minify(file_get_contents($sources['source_assets'] . '/js/' . $file));
                    if (!empty($jsmin)) {
                        $outFile = $jsDir . '/' . str_ireplace('.js', '-min.js', $file);
                        $fp = fopen($outFile, 'w');
                        if ($fp) {
                            fwrite($fp, $jsmin);
                            fclose($fp);
                            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, 'Created: ' . $outFile);
                        } else {
                            $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open min.js outfile: ' . $outFile);
                        }
                    }
                }

            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open JS directory.');
            }
        }


    // Build each Category
        if (!empty($this->myCategories)
        &&  is_array($this->myCategories))
            foreach ($this->myCategories as $category)
                if ($category instanceof ComponentResource)
                    $category->build($builder);
    // Clear some Memory
        unset($this->myCategories, $category);

    // Build the Action Menu
        if (!empty($this->myMenu))
            $this->myMenu->build($builder);

    // Bind Documents and Installation Options
        $attr = array(
            'license' => file_get_contents($sources['docs'] . 'license.txt'),
            'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
            'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
        );

        if (!empty($props['install.options'])) {
            $attr['setup-options'] = array(
                'source' => $sources['install_options'] . 'user.input.php',
            );
        } else {
            $attr['setup-options'] = array();
        }
        $builder->setPackageAttributes($attr);

    // ZIP up the Package.
        $builder->pack();

        /* report how long it took */
        $mtime= microtime();
        $mtime= explode(" ", $mtime);
        $mtime= $mtime[1] + $mtime[0];
        $tend= $mtime;
        $totalTime= ($tend - $tstart);
        $totalTime= sprintf("%2.4f s", $totalTime);

        $this->helpers->sendLog(xPDO::LOG_LEVEL_INFO, "Package Built.");
        $this->helpers->sendLog(xPDO::LOG_LEVEL_INFO, "Execution time: {$totalTime}");
        exit();
    }


/* *****************************************************************************
   General Support Functions
***************************************************************************** */

    /**
     * Helper function to create a directory for the Component.
     *
     * @param $path string - The directory to create.
     * @param $log boolean - (Optional) Whether to log this action
     *
     * @return boolean - Whether the directory exists after this function is run.
     *         Note that this does not mean the directory was created by this
     *         functin.
     */
    public function makeDir($path, $log = true)
    {//Initialize
        $success = $exists = is_dir($path);

    // Already exists
        if ($exists && $log) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Directory already exists: ' . $path);
        } // Create the path
        elseif (!$exists)
        {   $success = @mkdir($path, $this->dirPermission, true);
            if ($success && $log)
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Created Directory: ' . $path);
            elseif ($log)
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO,'Could not create Directory: ' . $path);
        }
    // Return Success or Failure
        return $success;
    }

/*
 * Not sure of the need for this... placeholder.
 */
    public function loadFromFileSystem()
    {

    }

/*
 * Reads a Transport Package and unbuilds it into a Build Script
 */
    public function extractPackage($file)
    {

    }

    public function initHelpers() {
        $this->source = $this->props['source'];
        $this->tplPath = $this->source . '_build/utilities/' . $this->props['tplDir'];
        if (substr($this->tplPath, -1) != "/") {
            $this->tplPath .= "/";
        }
        $this->dirPermission = $this->props['dirPermission'];
        $this->filePermission = $this->props['filePermission'];

        $this->replaceFields = array(
            '[[+packageName]]' => $this->props['packageName'],
            '[[+packageNameLower]]' => $this->props['packageNameLower'],
            '[[+packageDescription]]' => $this->props['packageDescription'],
            '[[+author]]' => $this->props['author'],
            '[[+email]]' => $this->props['email'],
            '[[+copyright]]' => $this->props['copyright'],
            '[[+createdon]]' => $this->props['createdon'],
            '[[+authorSiteName]]' => $this->props['authorSiteName'],
            '[[+authorUrl]]' => $this->props['authorUrl'],
            '[[+packageUrl]]' => $this->props['packageDocumentationUrl'],
            '[[+gitHubUsername]]' => $this->props['gitHubUsername'],
            '[[+gitHubRepository]]' => $this->props['gitHubRepository'],

        );

        $license = $this->helpers->getTpl('license');
        if (!empty($license)) {
            $license = $this->strReplaceAssoc($this->replaceFields, $license);
            $this->replaceFields['[[+license]]'] = $license;
        }
        unset($license);
    }

    /*public function getReplaceFields() {
        return $this->replaceFields;
    }*/
    /*public function replaceTags($text, $replaceFields = array()) {
        $replaceFields = empty ($replaceFields)? $this->replaceFields : $replaceFields;
        return $this->strReplaceAssoc($replaceFields, $text);
    }*/

    /**
     * Write a file to disk - non-destructive -- will not overwrite existing files
     * Creates dir if necessary
     *
     * @param $dir string - directory for file (should not have trailing slash!)
     * @param $fileName string - file name
     * @param $content - file content
     * @param string $dryRun string - if true, writes to stdout instead of file.
     */
    public function writeFile ($dir, $fileName, $content)
    {//For Quick Access
        $isDry = $this->dryRun;
        if ($isDry)
        {   $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    Would be creating: ' . $fileName . "\n");
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
        }
    // Protect ourselves...
        if (substr($dir, -1) != "/")
            $dir .= "/";
    // Make sure directory exists
        $canDo = $this->makeDir($dir, $this->dryRun);
        if (!$canDo)
            return false;

    /* write to stdout if dryRun is true */
        $file = $isDry
              ? 'php://output'
              : $dir . $fileName;
        if (empty($content)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '    No content for file ' . $fileName . ' (normal for chunks and templates until content is added)');
        }

        $fp = fopen($file, 'w');
        if ($fp)
        {//Output
            if (!$isDry)
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Creating ' . $file);

            fwrite($fp, $content);
            fclose($fp);
        // Set the permissions
            if (!$isDry)
                chmod($file, $this->filePermission);
        // Return Success
            $success = true;
        }
        else
        {   $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Could not write file ' . $file);
            $success = false;
        }
        if ($isDry) {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
        }
        return $success;

    }

    /**
     * Replaces all strings in $subject based on $replace associative array
     *
     * @param $replace array - associative array of key => value pairs
     * @param $subject string - text to do replacement in
     * @return string - altered text
     */
    public function strReplaceAssoc(array $replace, $subject)
    {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }

    /**
     * Recursive function copies an entire directory and its all descendants
     *
     * @param $source string - source directory
     * @param $destination string - target directory
     * @return bool - used only to control recursion
     */

    public function copyDir($source, $destination)
    {//Is a File (Most common first)
        if (is_file($source))
        {   if (copy($source, $destination))
                $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'File copied to: ' . $destination);
            else
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'Could not copy file: ' . $source);
        }
    // Is a Directory (Next most common)
        elseif (is_dir($source))
        {//Make sure the destination is available
            $canDo = $this->makeDir($destination);
        // Recursive Functions should end as soon as work is/cannot be done.
            if (!$canDo)
            {   $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'Could not copy directory: ' . $source);
                return false;
            }

        // Protect ourselves...
            if (substr($source, -1) != "/")
                $source .= "/";
            if (substr($destination, -1) != "/")
                $destination .= "/";

        // Exists - Proceed forward...
            $objects = scandir($source);
            if (sizeof($objects) > 0) {
                foreach ($objects as $file)
                {//Ignore List
                    if ($file == "."
                    ||  $file == ".."
                    ||  $file == '.git'
                    ||  $file == '.svn') {
                        continue;
                    }

                //Is a File (Most common first)
                    if (is_file($source . $file))
                    {//Check against ignores
                        if ($file == 'build.config.php')
                            continue;
                        elseif (strstr($file, 'config.php')
                        &&      $file != $this->props['packageNameLower'] . '.config.php')
                            continue;
                        else
                            $this->copyDir($source . $file, $destination . $file);
                    }
                    elseif (is_dir($source . $file))
                        $this->copyDir($source . $file, $destination . $file);
                }
            }
            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Directory copied to: ' . $destination);
            return true;
        }
    // Default Return
        return false;
    }

    /**
     * @param $values array - array from project config file
     * @param $intersectType string (modTemplateVarTemplate, modPluginEvent, etc.)
     * @param $mainObjectType string - (modTemplate, modSnppet, etc.)
     * @param $subsidiaryObjectType string - (modTemplate, modSnippet, etc.)
     * @param $fieldName1 string - intersect field name for main object.
     * @param $fieldName2 string - intersect field name for subsidiary object.
     */
    public function createIntersects($values, $intersectType, $mainObjectType, $subsidiaryObjectType, $fieldName1, $fieldName2 ) {
        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating ' . $intersectType . ' objects');


        if ($intersectType == 'modPluginEvent') {
            /* create new System Event Names record, if set in config */
            /* @var $obj modEvent */
            $newEvents = $this->props['newSystemEvents'];
            $newEventNames = empty($newEvents)? array() : explode(',', $newEvents);
            foreach($newEventNames as $newEventName) {
                $obj = $this->modx->getObject('modEvent', array('name' => $newEventName));
                if (!$obj) {
                    $obj = $this->modx->newObject('modEvent');
                    {
                        $obj->set('name', $newEventName);
                        $obj->set('groupname', $this->props['category']);
                        $obj->set('service', 1);

                        if ($obj && $obj->save()) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Created new System Event name: ' . $newEventName);
                        } else {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating System Event name: Could not save  ' . $newEventName);
                        }
                    }
                } else {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $newEventName . ': System Event name already exists');
                }
            }
        }


        if (empty($values)) {
            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': value array is empty');
            return;
        }
        foreach ($values as $mainObjectName => $subsidiaryObjectNames) {
            if (empty($mainObjectName)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': main object name is empty');
                continue;
            }

            $alias = $this->getNameAlias($mainObjectType);
            if ($mainObjectType == 'modTemplate' && ($mainObjectName == 'default' || $mainObjectName == 'Default')) {
                $defaultTemplateId = $this->modx->getOption('default_template');
                $mainObject = $this->modx->getObject('modTemplate', $defaultTemplateId);
            } else {
                $mainObject = $this->modx->getObject($mainObjectType, array($alias => $mainObjectName) );
            }
            if (! $mainObject) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get main object ' . $mainObjectName);
                continue;
            }
            $subsidiaryObjectNames = explode(',', $subsidiaryObjectNames);
            if (empty($subsidiaryObjectNames)) {
                $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name list is empty');
                continue;
            }
            foreach ($subsidiaryObjectNames as $subsidiaryObjectName) {
                if (empty($subsidiaryObjectName)) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name is empty');
                    continue;
                }
                if (strstr($subsidiaryObjectName, ':')) {
                    $s = explode(':', $subsidiaryObjectName);
                    $subsidiaryObjectName = trim($s[0]);
                    $subsidiaryObjectType = trim($s[1]);
                }
                $alias = $this->getNameAlias($subsidiaryObjectType);
                $subsidiaryObjectType = $intersectType == 'modPluginEvent' ? 'modEvent' : $subsidiaryObjectType;
                $subsidiaryObject = $this->modx->getObject($subsidiaryObjectType, array($alias => $subsidiaryObjectName));
                if (! $subsidiaryObject) {
                    $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get subsidiary object ' . $subsidiaryObjectName);
                    continue;
                }
                if ($mainObjectType == 'modTemplate' && $subsidiaryObjectType == 'modResource') {
                    /* @var $mainObject modTemplate */
                    /* @var $subsidiaryObject modResource */
                    if ($subsidiaryObject->get('template') != $mainObject->get('id')) {
                        $subsidiaryObject->set('template', $mainObject->get('id'));
                        if ($subsidiaryObject->save()) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Connected ' . $mainObjectName . ' Template to ' . $subsidiaryObjectName . ' Resource');
                        } else {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType);
                        }
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $mainObjectName . ' Template is already connected to ' . $subsidiaryObjectName . ' Resource');
                    }
                    continue;
                } else {
                    $fields = array(
                        $fieldName1 => $mainObject->get('id'),
                        $fieldName2 => $intersectType == 'modPluginEvent' ? $subsidiaryObjectName : $subsidiaryObject->get('id'),

                    );
                    $intersect = $this->modx->getObject($intersectType, $fields);
                    /* @var $intersect xPDOObject */
                    if (!$intersect) {
                        $intersect = $this->modx->newObject($intersectType);
                        $intersect->set($fieldName1, $mainObject->get('id'));
                        $intersect->set($fieldName2, $intersectType == 'modPluginEvent' ? $subsidiaryObjectName : $subsidiaryObject->get('id'));
                        if ($intersectType == 'modElementPropertySet') {
                            $intersect->set('element_class', $subsidiaryObjectType);
                        }

                        if ($intersect && $intersect->save()) {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Created intersect ' . ' for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                        } else {
                            $this->helpers->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Failed to save intersect');
                        }
                    } else {
                        $this->helpers->sendLog(MODX::LOG_LEVEL_INFO, '    Intersect ' . $intersectType . ' already exists for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                    }
                }
            }
        }
    }
    public function removeObjects() {
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
        );

        foreach($objects as $object => $type) {
            $name = 'name';
            if ($type == 'modTemplate') $name = 'templatename';
            if ($type == 'modResource') $name = 'pagetitle';
            if ($type == 'modSystemSetting') $name = 'key';
            $obj = $this->modx->getObject($type, array($name => $object));
            if (! $obj) {
                $this->helpers->sendLog(MODX_LOG_LEVEL_ERROR, 'Could not find ' . $type . ' ' . $object);
            } else {
                $obj->remove();
            }
            $c = $this->modx->getObject('modCategory', array('category' => 'Example'));
            if ($c) {
                $c->remove();
            }
        }
    }

}

/* autoload should do these */

// Include Base Classes
//    require_once('objectadapter.class.php');
// Include Class Hierarchy
//    require_once('namespaceadapter.class.php');
//    require_once('categoryadapter.class.php');
