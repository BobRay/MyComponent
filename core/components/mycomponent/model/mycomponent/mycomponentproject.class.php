<?php

class MyComponentProject
{   $modx;
    
    $myName;
    $myVersion;
    
// List of Paths for MC and the Project
    $myPaths;
// List of all Objects
    $myObjects = array();
    
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
        return $this->myPaths['mc-core'] == '';
    }
    
/* *****************************************************************************
   Construction and Support Functions (in MODxObjectAdapter)
***************************************************************************** */
    public function __construct()
    {//Get MODx first
        $this->initMODx();
    // Build the Folder Structure
        $this->initPaths();
    // Load the Build Config
        $this->initComponent();
    }

    /* Instantiate MODx -- if this require fails, check your
     * _build/build.config.php file
     */
    public function initMODx()
    {//Only redefine if not defined
        if (!defined('MODX_CORE_PATH'))
        {   require_once $sources['build'].'build.config.php';
            require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
            
            $modx= new modX();
        }
        else
            global $modx;
    
    // Initialize and set up logging
        $modx->initialize('mgr');
        $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
        $modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
    // Point to modx
        $this->modx = $modx;
    }
    
    public function initComponent()
    {//Load the Config File
        $config = $this->getPath('root') . '/_build/build.config.php';
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

    /**
     * Sets up the Path variables for the Component Project. Runs automatically
     * on __construct. 
     */
    public function initPaths()
    {//For Quick Access
        $modx = $this->modx;
    // Init as blank array()
        $paths = array();
    // Set the MODx path
        $paths['modx'] = '';
    // Set the Root paths for MyComponent
    /* IMPORTANT: Namespace Hardcoded!! */
        $ns = $modx->getObject('modNamespace', array('key' => 'mycomponent'));
        $paths['mc-core'] = !empty($ns)
            ? $ns->get('path')
            : is_dir(MODX_ASSETS_PATH . 'mycomponents/mycomponent/core/components/mycomponent/')
                ? MODX_ASSETS_PATH . 'mycomponents/mycomponent/core/components/mycomponent/'
                : '';
            $paths['mc-model'] = $paths['mc-core'] != ''
                ? $paths['mc-core'] . 'model/mycomponent/'
                : '';
                $paths['mc-new'] = $paths['mc-core'] != ''
                    ? $paths['mc-model'] . 'model/mycomponent/newbuild/'
                    : '';
                $paths['mc-tpl'] = $paths['mc-core'] != ''
                    ? $paths['mc-model'] . 'model/mycomponent/buildtpls/'
                    : '';
            $paths['mc-elements'] = $paths['mc-core'] != ''
                ? $paths['mc-core'] . 'elements/'
                : '';
                $paths['mc-tpl'] = $paths['mc-core'] != ''
                    ? $paths['mc-elements'] . 'chunks/'
                    : '';
        $paths['mc-assets'] = !empty($ns)
            ? $ns->get('assets_path')
            : is_dir(MODX_ASSETS_PATH . 'mycomponents/mycomponent/assets/components/mycomponent/')
                ? MODX_ASSETS_PATH . 'mycomponents/mycomponent/assets/components/mycomponent/'
                : '';
    // Set the Root path for this Component
        $paths['me'] = dirname(dirname(dirname(__FILE__)));
        // Set the Basic Necessary Paths
            $paths['core']      = $paths['me'] . 'core/components/' . $name . '/';
                $paths['control']   = $paths['core'] . 'controllers/';
                $paths['docs']      = $paths['core'] . 'docs/';
                $paths['elements']  = $paths['core'] . 'elements/';
                $paths['lexicon']   = $paths['core'] . 'lexicon/';
                $paths['model']     = $paths['core'] . 'model/' . $name . '/';
                $paths['process']   = $paths['core'] . 'processors/';
            $paths['assets']    = $paths['me'] . 'assets/components/' . $name . '/';
                $paths['css']       = $paths['assets'] . 'css/';
                $paths['js']        = $paths['assets'] . 'js/';
                $paths['images']    = $paths['assets'] . 'images/';
            $paths['build']     = $paths['me'] . '_build/';
                $paths['data']      = $paths['build'] . 'data/';
                    $paths['propeties'] = $paths['data'] . 'properties/';
                $paths['resolve']   = $paths['build'] . 'resolvers/';
                $paths['validate']  = $paths['build'] . 'validators/';
    // Set to Class Member
        $this->myPaths = $paths;
    }
    
    
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
    private function getCodeDir ($targetCore, $type) {    }
    

/* *****************************************************************************
   Bootstrap and Support Functions
***************************************************************************** */

    public function newComponent()
    {//Only run if MC is installed
        if (!$this->isMCInstalled())
        {   $this->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to create a new MyComponent Project!');
            return;
        }
        
    // This must happen first
        $this->newPaths();
        
    // Installation Options Scripts
        $this->newInstallOptions();
    }
    
    public function newPaths()
    {//For Quick Access
        $paths = $this->paths;
        $needs = $this->hasObjects;
    
    // Iterate through array
        foreach($needs as $key => $value)
        {   if ($value == true)
                $this->makeDir($paths[$key]);
        }
    }
    
    /** Initializes class variables */
    public function init($configPath) {
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
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Component: ' . $this->props['packageName']);
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Source: ' . $this->source);
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Target Base: ' . $this->targetBase);
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Target Core: ' . $this->targetCore);
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Target Assets: ' . $this->targetAssets);

        $this->sendLog(MODX::LOG_LEVEL_INFO, '--------------------------------------------------');
    }

    /** Creates build transport and config files, (optionally) lexicon files, doc file,
     *  readme.md, and full _build directory with utilities if set in project config file */
    public function createBasics() {
        $defaults = $this->props['defaultStuff'];

        /* Transfer build and build config files */

        $dir = $this->targetBase . '_build';
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating build files');
        $fileName = 'build.transport.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->getTpl($fileName);
            $tpl = $this->replaceTags($tpl);
            $this->writeFile($dir, $fileName, $tpl);
        } else {
            $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
        }
        $fileName = 'build.config.php';
        if (!file_exists($dir . '/' . $fileName)) {
            copy($this->source . '_build/build.config.php', $dir . '/' . 'build.config.php');
        } else {
            $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');

        }
        
        if (isset ($defaults['utilities']) && $defaults['utilities']) {
            $fromDir = $this->source . '_build/utilities/';
            $toDir = $this->targetBase . '_build/utilities/';
            if (! is_dir($toDir)) {
                $this->sendLog(MODX::LOG_LEVEL_INFO, 'Copying Utilities directory');
                $this->copyDir($fromDir, $toDir);
            } else {
                $this->sendLog(MODX::LOG_LEVEL_INFO, '    Utilities directory already exists');
            }
        }

        if (isset ($this->props['languages']) &&  ! empty($this->props['languages'])) {
            $this->sendLog(MODX::LOG_LEVEL_INFO,'Creating Lexicon files');
            $lexiconBase = $this->targetCore . 'lexicon/';
            foreach($this->props['languages'] as $language => $languageFiles) {
                $dir = $this->targetCore . 'lexicon/' . $language;
                $files = !empty($languageFiles)? explode(',', $languageFiles) : array();
                foreach($files as $file){
                    $fileName = $file . '.inc.php';
                    if (! file_exists($dir . '/' . $fileName)){
                        $tpl = $this->getTpl('phpfile.php');
                        $tpl = str_replace('[[+elementName]]', $language . ' '. $file . ' topic', $tpl);
                        $tpl = str_replace('[[+description]]', $language . ' ' . $file . ' topic lexicon strings', $tpl);
                        $tpl = str_replace('[[+elementType]]', 'lexicon file', $tpl);
                        $tpl = $this->replaceTags($tpl);
                        $this->writeFile($dir, $fileName, $tpl);
                    } else {
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $language . ':' . $fileName . ' file already exists');
                    }

                }
            }
        }
        if (isset ($defaults['docs']) && ! empty($defaults['docs'])) {
            $this->sendLog(MODX::LOG_LEVEL_INFO,'Creating doc files');
            $toDir = $this->targetCore . 'docs';
            $docs = !empty($defaults['docs'])? explode(',', $defaults['docs']) : array();
            foreach($docs as $doc) {
                if (! file_exists($toDir . '/' . $doc )) {
                    $tpl = $this->getTpl($doc);
                    $tpl = $this->replaceTags($tpl);
                    $this->writeFile($toDir, $doc, $tpl);
                } else {
                    $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $doc . ' file already exists');
                }
            }
        }
        if (isset ($defaults['readme.md']) && $defaults['readme.md']) {
            if (! file_exists($this->targetBase . 'readme.md')) {
                $tpl = $this->getTpl('readme.md');
                $tpl = $this->replaceTags($tpl);
                $this->writeFile($this->targetBase, 'readme.md', $tpl);
            } else {
                $this->sendLog(MODX::LOG_LEVEL_INFO, 'readme.md file already exists');
            }

        }

        return true;
    }

    /** Creates assets directories and (optionally) empty css and js files
     * if set in project config file */
    public function newAssetsDirs() {
        if (! $this->props['hasAssets']) {
            return;
        }
        $optionalDirs = !empty($this->props['assetsDirs'])? $this->props['assetsDirs'] : array();
        $this->sendLog(MODX::LOG_LEVEL_INFO,'Creating Assets directories');
        foreach($optionalDirs as $dir => $val) {
            $targetDir = $this->targetAssets . $dir;
            if ($val && (! is_dir($targetDir)) ) {
                if (mkdir($targetDir, $this->dirPermission, true)) {
                    $this->sendLog(MODX::LOG_LEVEL_INFO,'    Created ' . $targetDir . ' directory');
                }
            } else {
                $this->sendLog(MODX::LOG_LEVEL_INFO,'    ' . $targetDir . ' directory already exists');
            }
            if ($dir == 'css' || $dir == 'js') {
                $path = $this->targetAssets . $dir;
                $fileName = $this->packageNameLower . '.' . $dir;
                if (!file_exists($path . '/' . $fileName)) {
                    $tpl = $this->getTpl($dir);
                    $tpl = $this->replaceTags($tpl);
                    $this->writeFile($path, $fileName, $tpl);
                } else {
                    $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }

            }
        }
    }

    /** Creates example file for user input during install if set in project config file */
    public function newInstallOptions() 
    {//For Quick Access
        $path = $this->myPaths['build'] . 'install.options/';
        $filename = 'user.input.php';
        
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (! empty($iScript)) 
        {//Make sure we have the directory 
            if(!is_dir($path))
            {
                
            }

        // Check if the file exists
            if (file_exists($path . $fileName)) 
                $this->sendLog(MODX::LOG_LEVEL_INFO, 'Install Options already exist at: ' $path . $fileName);
            else
            {//Get the Templatized Basic file
                $tpl = $this->getTpl($fileName);
                $tpl = $this->replaceTags($tpl);
                $this->writeFile($path, $fileName, $tpl);
                $this->sendLog(MODX::LOG_LEVEL_INFO, 'Created Install Options at: ' . $path . $filename);
            } 
        }
    }

    /** Deprecated: Called from NamespaceAdapter->addToMODx */
    public function createNewSystemSettings() {    }
    
    /** Creates example file for user input during install if set in project config file */
    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (! empty($iScript)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating Install Options');
            $dir = $this->targetBase . '_build/install.options';
            $fileName = 'user.input.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $tpl = $this->getTpl($fileName);
                $tpl = $this->replaceTags($tpl);
                $this->writeFile($dir, $fileName, $tpl);
            } else {
                $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }
    
    
    /** Creates propertyset objects in MODX install if set in project config file.
     * Create the property set's properties in the Manager and export them
     * with exportObjects */

    public function createPropertySets() {
        $propertySets = $this->modx->getOption('propertySets', $this->props, '' );
        if (! empty($propertySets)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating property sets');
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
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    Created ' . $name . ' property set object');
                    } else {
                        $this->sendLog(MODX::LOG_LEVEL_ERROR, '    Could not create ' . $name . ' property set object');
                    }

                } else {
                    /* do this in case the set is leftover from a bad install
                     * and has the wrong category ID (won't show in tree). */
                    if ($set->get('category') != $this->categoryId) {
                        $set->set('category', $this->categoryId);
                        $set->save();
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    Updated ' . $name . ' property set category');
                    } else {
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $name . ' property set already exists');
                    }
                }
            }
        }
    }

    /** Creates validators if set in project config file */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating validators');
            $dir = $this->targetBase . '_build/validators';

            $validators = explode(',', $validators);
            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $fileName = $this->packageNameLower . '.' . 'validator.php';
                } else {
                    $fileName = $validator . '.' . 'validator.php';
                }
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->getTpl('genericvalidator.php');
                    $tpl = $this->replaceTags($tpl);
                    $this->writeFile($dir, $fileName, $tpl);
                } else {
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    '  . $fileName . ' already exists');
                }
            }
        }
    }
    /** Creates additional resolvers specified in project config file */
    public function createExtraResolvers() {
        $resolvers = $this->modx->getOption('resolvers', $this->props, '');
        if (!empty($resolvers)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating extra resolvers');
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
                    $tpl = $this->getTpl('genericresolver.php');
                    $tpl = $this->replaceTags($tpl);
                    $this->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }
            }
        }
    }

    /**
     * Connects Property Sets to Elements and creates a resolver to connect them
     * during the install.
     */
    public function connectPropertySetsToElements() {
        $propertySets = $this->props['propertySetElements'];

        if (!empty($propertySets)) {
            $this->createIntersects($propertySets,'modElementPropertySet','modPropertySet','modElement', 'property_set','element');
        }
        /* Create Resolver */
        if (!empty($propertySets)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating tv resolver');
            $tpl = $this->getTpl('propertysetresolver.php');
            $tpl = $this->replaceTags($tpl);
            if (empty($tpl)) {
                $this->sendLog(MODX::LOG_LEVEL_ERROR, 'propertysetresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'propertyset.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->getTpl('propertysetresolvercode.php');
                $codeTpl = str_replace('<?php', '', $codeTpl);

                foreach ($propertySets as $propertySet => $elements) {
                    $tempCodeTpl = str_replace('[[+propertySet]]', $propertySet, $codeTpl);
                    $tempCodeTpl = str_replace('[[+elements]]', $elements, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $this->writeFile($dir, $fileName, $tpl);
            } else {
                $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }

    }
    /** Creates "starter" class files specified in project config file */
    public function createClassFiles() {
        /* @var $element modElement */
        $classes = $this->modx->getOption('classes', $this->props, array());
        $classes = !empty($classes) ? $classes : array();
        if (!empty($classes)) {
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating class files');
            $baseDir = $this->targetCore . 'model';
            foreach($classes as $className => $data) {
                $data = explode(':', $data);
                if (!empty($data[1])) {
                    $dir = $baseDir . '/' . $data[0] . '/' . $data[1];
                } else {  /* no directory */
                    $dir = $baseDir . '/' . $data[0];
                }
                $fileName = strtolower($className) . '.class.php';
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl );
                    $tpl = str_replace('[[+className]]', $className, $tpl);
                    $tpl = $this->replaceTags($tpl);
                    $this->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
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
        {   $this->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to import the Project into MODx!');
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
        {   $this->sendLog(MODX::LOG_LEVEL_ERROR, 'MyComponent must be installed to export the Project from MODx!');
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
        $this->sendLog();
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
        {   $this->sendLog(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');
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
                            $this->sendLog(modX::LOG_LEVEL_INFO, 'Created: ' . $outFile);
                        } else {
                            $this->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open min.js outfile: ' . $outFile);
                        }
                    }
                }
        
            } else {
                $this->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open JS directory.');
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
        
        $this->sendLog(xPDO::LOG_LEVEL_INFO, "Package Built.");
        $this->sendLog(xPDO::LOG_LEVEL_INFO, "Execution time: {$totalTime}");
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
        if ($exists && $log)
            $this->sendLog(MODX::LOG_LEVEL_INFO,'Directory already exists: ' . $path);
    // Create the path
        elseif (!$exists)
        {   $success = mkdir($path, $this->dirPermission, true)
            if ($success && $log)
                $this->sendLog(MODX::LOG_LEVEL_INFO,'Created Directory: ' . $path);
            elseif ($log)
                $this->sendLog(MODX::LOG_LEVEL_INFO,'Could not create Directory: ' . $path);
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
            '[[+packageUrl]]' => $this->props['packageUrl'],
            '[[+gitHubUsername]]' => $this->props['gitHubUsername'],
            '[[+gitHubRepository]]' => $this->props['gitHubRepository'],

        );
        
        $license = $this->getTpl('license');
        if (!empty($license)) {
            $license = $this->strReplaceAssoc($this->replaceFields, $license);
            $this->replaceFields['[[+license]]'] = $license;
        }
        unset($license);
    }
    
    public function getReplaceFields() {
        return $this->replaceFields;
    }
    public function replaceTags($text, $replaceFields = array()) {
        $replaceFields = empty ($replaceFields)? $this->replaceFields : $replaceFields;
        return $this->strReplaceAssoc($replaceFields, $text);
    }

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
        {   $this->sendLog(modX::LOG_LEVEL_INFO, '    Would be creating: ' . $fileName . "\n");
            $this->sendLog(modX::LOG_LEVEL_INFO, " --- Begin File Content --- ");
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
            $this->sendLog(MODX::LOG_LEVEL_ERROR, '    No content for file ' . $fileName . ' (normal for chunks and templates until content is added)');
        }

        $fp = fopen($file, 'w');
        if ($fp) 
        {//Output
            if (!$isDry)
                $this->sendLog(MODX::LOG_LEVEL_INFO, '    Creating ' . $file);

            fwrite($fp, $content);
            fclose($fp);
        // Set the permissions
            if (!$isDry)
                chmod($file, $this->filePermission);
        // Return Success
            $success = true;
        }
        else
        {   $this->sendLog(MODX::LOG_LEVEL_INFO, '    Could not write file ' . $file);
            $success = false;
        }        
        if ($isDry)
            $mc->sendLog(modX::LOG_LEVEL_INFO, " --- End File Content --- \n");
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
                $this->sendLog(MODX::LOG_LEVEL_INFO, 'File copied to: ' . $destination);
            else
                $this->sendLog(MODX::LOG_LEVEL_ERROR, 'Could not copy file: ' . $source);
        }
    // Is a Directory (Next most common)
        elseif (is_dir($source))
        {//Make sure the destination is available
            $canDo = $this->makeDir($destination);
        // Recursive Functions should end as soon as work is/cannot be done.
            if (!$canDo)
            {   $this->sendLog(MODX::LOG_LEVEL_ERROR, 'Could not copy directory: ' . $source);
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
            $this->sendLog(MODX::LOG_LEVEL_INFO, 'Directory copied to: ' . $destination);
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
        $this->sendLog(MODX::LOG_LEVEL_INFO, 'Creating ' . $intersectType . ' objects');


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
                            $this->sendLog(MODX::LOG_LEVEL_INFO, '    Created new System Event name: ' . $newEventName);
                        } else {
                            $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating System Event name: Could not save  ' . $newEventName);
                        }
                    }
                } else {
                    $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $newEventName . ': System Event name already exists');
                }
            }
        }


        if (empty($values)) {
            $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': value array is empty');
            return;
        }
        foreach ($values as $mainObjectName => $subsidiaryObjectNames) {
            if (empty($mainObjectName)) {
                $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': main object name is empty');
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
                $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get main object ' . $mainObjectName);
                continue;
            }
            $subsidiaryObjectNames = explode(',', $subsidiaryObjectNames);
            if (empty($subsidiaryObjectNames)) {
                $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name list is empty');
                continue;
            }
            foreach ($subsidiaryObjectNames as $subsidiaryObjectName) {
                if (empty($subsidiaryObjectName)) {
                    $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': subsidiary object name is empty');
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
                    $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Could not get subsidiary object ' . $subsidiaryObjectName);
                    continue;
                }
                if ($mainObjectType == 'modTemplate' && $subsidiaryObjectType == 'modResource') {
                    /* @var $mainObject modTemplate */
                    /* @var $subsidiaryObject modResource */
                    if ($subsidiaryObject->get('template') != $mainObject->get('id')) {
                        $subsidiaryObject->set('template', $mainObject->get('id'));
                        if ($subsidiaryObject->save()) {
                            $this->sendLog(MODX::LOG_LEVEL_INFO, '    Connected ' . $mainObjectName . ' Template to ' . $subsidiaryObjectName . ' Resource');
                        } else {
                            $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType);
                        }
                    } else {
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $mainObjectName . ' Template is already connected to ' . $subsidiaryObjectName . ' Resource');
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
                            $this->sendLog(MODX::LOG_LEVEL_INFO, '    Created intersect ' . ' for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                        } else {
                            $this->sendLog(MODX::LOG_LEVEL_ERROR, '   Error creating intersect ' . $intersectType . ': Failed to save intersect');
                        }
                    } else {
                        $this->sendLog(MODX::LOG_LEVEL_INFO, '    Intersect ' . $intersectType . ' already exists for ' . $mainObjectType . ' ' . $mainObjectName . ' -- ' . $subsidiaryObjectType . ' ' . $subsidiaryObjectName);
                    }
                }
            }
        }
    }
}

// Include Base Classes
    require_once($root.'classes/'.'objectadapter.class.php');
// Include Class Hierarchy
    require_once($root.'classes/'.'namespaceadapter.class.php');
    require_once($root.'classes/'.'categoryadapter.class.php');
