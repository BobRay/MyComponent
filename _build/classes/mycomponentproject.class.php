<?php

class MyComponentProject
{   $modx;
    $root = '';
    $folder;
    
    $myName;
    $myVersion;

// List of all MODx Objects
    $myObjects = array();
    
/* *****************************************************************************
   Construction and Support Functions (in MODxObjectAdapter)
***************************************************************************** */
    public function __construct(&$modx, $name, $path)
    {//Get MODx first
        $this->modx =& $modx;
        $this->name = $name;
                
    }

/* Instantiate MODx -- if this require fails, check your
 * _build/build.config.php file
 */
    private function getMODx()
    {
        require_once $sources['build'].'build.config.php';
        require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
        $modx= new modX();
        $modx->initialize('mgr');
        $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
        $modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
    
        $this->modx = $modx;
    }
    
    private function loadConfig()
    {//Set the Root path for this Component
    // Assumes that the Class is in _build/classes/
        $root = dirname(dirname(dirname(__FILE__)));
        $this->core($root.'core/');
        $this->assets($root.'assets/');
        $this->build($root.'_build/');
        
        $config = $root.'/_build/config.php';
    // Get the Config File
        if (file_exists($config)) 
            $properties = @include $config;
        else
            die('Could not find main config file at ' . $config);
    // Make sure that we get usable values
        if (empty($properties))
            die('Config File was not set up correctly: ' . $config);
        
    // Initialize Component
        $this->copyAssets = file_exists($this->assets);
        $this->copyCore = file_exists($this->core);
        $this->runBuild = file_exists($this->build);
        
        $hasPropertySets = !empty($props['propertySets']);
        $hasMenu = !empty($props['menus']); /* Add items to the MODx Top Menu */
    // This will only work if we have a Core directory
        if ($this->copyCore)
        {
        // Copy Documents
            $this->copyDocuments = file_exists($this->core.'docs/');
        // Copy the Lexicon
            $this->copyLexicon = file_exists($this->core.'lexicon/');
        // Copy and Install Dependencies
            $this->copyDependencies = file_exists($this->core.'packages/');
        }
    // This will only work if we have a Build directory
        if ($this->runBuild)
        {   $this->pathData = $this->build.'data/';
        
            $this->addValidators = file_exists($this->build.'validators/');
            
            $hasSettings = true;
            $hasTemplateVariables = !empty($props['elements']['modTemplateVar']);
            
            $hasSetupOptions = !empty($props['install.options']); /* HTML/PHP script to interact with user */
            $hasResources = !empty($props['resources']);
        }
    // This will only work if we have a Core AND Build directory
        if ($this->copyCore 
        &&  $this->runBuild)
        {
            $hasSnippets = !empty($props['elements']['modSnippet']);
            $hasChunks = !empty($props['elements']['modChunk']);
            $hasTemplates = !empty($props['elements']['modTemplate']);
            $hasPlugins = !empty($props['elements']['modPlugin']);
        }
    // This will only work if we have Assets
        if ($this->copyAssets)
        {
            $this->minify = $properties['minifyJS'];
        }
        else
        {
            $this->minify = false;
        }
    }
    
    private function loadChildren()
    {   if ($this->runBuild)
        {//For quick access
            $data = $this->pathData;
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
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */
    public function newComponent()
    {//Initialize MODx
        getMODx();
    // Load the Configuration - This will give us the object hierarchy
        loadConfig();
        
    }
    
    /** Initializes class variables */
    private function init($configPath) {
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
        $this->helpers->init();

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
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Component: ' . $this->props['packageName']);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Source: ' . $this->source);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Base: ' . $this->targetBase);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Core: ' . $this->targetCore);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Assets: ' . $this->targetAssets);

        $this->modx->log(MODX::LOG_LEVEL_INFO, '--------------------------------------------------');
    }

    /** Creates assets directories and (optionally) empty css and js files
     * if set in project config file */
    public function newAssetsDirs() {
        if (! $this->props['hasAssets']) {
            return;
        }
        $optionalDirs = !empty($this->props['assetsDirs'])? $this->props['assetsDirs'] : array();
        $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating Assets directories');
        foreach($optionalDirs as $dir => $val) {
            $targetDir = $this->targetAssets . $dir;
            if ($val && (! is_dir($targetDir)) ) {
                if (mkdir($targetDir, $this->dirPermission, true)) {
                    $this->modx->log(MODX::LOG_LEVEL_INFO,'    Created ' . $targetDir . ' directory');
                }
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO,'    ' . $targetDir . ' directory already exists');
            }
            if ($dir == 'css' || $dir == 'js') {
                $path = $this->targetAssets . $dir;
                $fileName = $this->packageNameLower . '.' . $dir;
                if (!file_exists($path . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl($dir);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($path, $fileName, $tpl);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }

            }
        }
    }

/* *****************************************************************************
   Import Objects and Support Functions 
***************************************************************************** */

    function importComponent($overwrite = false)
    {//In case this is called from newComponent()...
        if (empty($modx))
        {//Initialize MODx
            getMODx();
        // Load the Configuration
            loadConfig();
        // Walks the directories to get the Children
            scanChildren();
        }
        
    // Crawl through the list top-down
        $objects = $this->myObjects;
        foreach ($objects as $child)
            if (!empty($child))
                $child->addToMODx();
    }

/* *****************************************************************************
   Export Objects and Support Functions 
***************************************************************************** */

/*
 * This function does the real work of getting the package objects from the
 * MODx database. 
 */
    function exportComponent($overwrite = false)
    {//Initialize MODx
        getMODx();
    // Load the Configuration
        loadConfig();
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
        $this->sendLog();
    }
    
/* *****************************************************************************
   Build Vehicle and Support Functions 
***************************************************************************** */

/*
 * Builds the transport file from the folder hierarchy of the package.
 */
    function buildComponent()
    {//Begin tracking the time to build
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $tstart = $mtime;
        set_time_limit(0);
        
    // Initialize MODx
        getMODx();
    // Load the Configuration
        loadConfig();
    // Load the Relevant Children
        loadChildren();

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
        $modx->loadClass('transport.modPackageBuilder','',false, true);
        
        
        $builder = new modPackageBuilder($modx);
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
        unset($this->myResources, $resource)
        
        
        /* minify JS */
        
        if ($this->minify) 
        {   $modx->log(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');
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
                            $modx->log(modX::LOG_LEVEL_INFO, 'Created: ' . $outFile);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Could not open min.js outfile: ' . $outFile);
                        }
                    }
                }
        
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Could not open JS directory.');
            }
        }
        
        
    // Build each Category
        if (!empty($this->myCategories)
        &&  is_array($this->myCategories))
            foreach ($this->myCategories as $category)
                if ($category instanceof ComponentResource)
                    $category->build($builder);
    // Clear some Memory
        unset($this->myCategories, $category)
        
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
        
        $modx->log(xPDO::LOG_LEVEL_INFO, "Package Built.");
        $modx->log(xPDO::LOG_LEVEL_INFO, "Execution time: {$totalTime}");
        exit();
    }
    
/*
 *
 */
    funtion loadFromFileSystem()
    {
        
    }
    
/*
 * Reads a Transport Package and unbuilds it into a Build Script
 */
    private function extractPackage($file)
    {
        
    }
}

// Include Base Classes
    require_once($root.'classes/'.'vehicleadapter.class.php');
// Include Class Hierarchy
    require_once($root.'classes/'.'namespaceadapter.class.php');
    require_once($root.'classes/'.'categoryadapter.class.php');
