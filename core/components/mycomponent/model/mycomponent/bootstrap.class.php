<?php
/**
 * bootstrap class file for MyComponent extra
 *
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 * Created on 08-11-2012
 *
 * MyComponent is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */

/**
 * Description
 * -----------
 * methods used by bootstrap.php
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package mycomponent
 **/


class Bootstrap {
    /* @var $modx modX - MODX object */
    public $modx;
    /* @var $props array  - $scriptProperties array */
    public $props;
    /* @var $helpers Helpers  - class of helper functions */
    public $helpers;
    public $packageName;
    public $packageNameLower;
    public $source;
    public $targetBase;
    public $targetCore;
    public $targetAssets;
    public $corePath;
    public $assetsPath;
    public $tplPath; /* path to element Tpl files */
    public $categoryId;
    public $makeStatic; /* array of objects to make static (comma,separated list in config) */
    public $dirPermission;
    public $filePermission;

    function  __construct(&$modx, &$props = array()) {
                $this->modx =& $modx;
                $this->props =& $props;
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
    /** Creates category object in DB and sets $this->categoryId */
    public function createCategory() {

        /* @var $categoryObj modCategory */
        $category = $this->props['category'];
        $categoryObj = $this->modx->getObject('modCategory', array('category' => $category ));
        if (! $categoryObj) {
            $categoryObj = $this->modx->newObject('modCategory', array('category' => $category));
             if ($categoryObj->save()) {
                 $this->modx->log(MODX::LOG_LEVEL_INFO, 'Created category Object: ' . $categoryObj->get('category'));
             };
            $this->categoryId = $categoryObj->get('id');
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, $categoryObj->get('category') . ' category object already exists ');
            $this->categoryId = $categoryObj->get('id');
        }
        unset($category, $categoryObj);
    }

    /** Creates namespace object in DB */
    public function createNamespace() {

        /* @var $namespaceObj modNamespace */
        $namespace = $this->props['packageNameLower'];
        $namespaceObj = $this->modx->getObject('modNamespace', array('name' => $namespace));
        if (!$namespaceObj) {
            $namespaceObj = $this->modx->newObject('modNamespace');
            $namespaceObj->set('name', $namespace);
            $namespaceObj->set('path' , '{core_path}components/' . $this->packageNameLower . '/');
            if ($this->props['hasAssets']) {
                $namespaceObj->set('assets_path', '{assets_path}components/' . $this->packageNameLower . '/');
            }
            if ($namespaceObj->save()) {
                $this->modx->log(MODX::LOG_LEVEL_INFO, 'Created namespace Object: ' . $namespaceObj->get('name'));
            }
        }
        else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, $this->packageNameLower . ' namespace object already exists ');
        }
        unset($namespace, $namespaceObj);
    }
    /** Calls $this->createElement to create element files and/or objects */
    public function createElements() {
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Category ID: ' . $this->categoryId);
        $allElements = $this->modx->getOption('elements', $this->props, '');
        $allElements = !empty($allElements)? $allElements : array();
        foreach ($allElements as $elementType => $elements) {
            $elements = !empty($elements)? explode(',', $elements) : array();
            foreach ($elements as $name) {
                if (! empty ($name)) {
                    $this->createElement($name, $elementType);
                }
            }
        }
    }

    /**
     * Creates an element (code file and or MODX object) based on project config file
     *
     * @param $name string - Name of Element (e.g., 'MySnippet')
     * @param $type - Element type (e.g. modPlugin, modTemplateVar)
     */
    public function createElement ($name, $type) {

        //echo "\nDIRNAME: " . $fileNameType;
        $fileName = $this->helpers->getFileName($name, $type);

        $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating ' . $type . ': ' . $name);

        if ($this->props['createElementFiles']) {
            $this->createCodeFile($name, $type);
            // echo "\nCODE_PATH: " . $codePath . "\n";
        }
        if ($this->props['createElementObjects']) {
            $this->createElementObject($name, $type);
        }

    }

    /**
     * Creates a code file for an element if set in project config file
     *
     * @param $name string - lowercase filename (without extension or type
     * @param $type string - modPlugin, modSnippet etc.
     */
    public function createCodeFile($name, $type) {
        $dir = $this->helpers->getCodeDir($this->targetCore, $type);
        $fileName = $this->helpers->getFileName($name, $type);
        // echo "\nDIR: " . $dir . "\n" . 'FILENAME: ' . $fileName . "\n" . "TYPE: " . $type . "\n";
        if (empty($fileName)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    skipping ' . $type . ' file -- needs no code file');
        } else {
            if (!file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($type);

                /* use 'phpfile.tpl' as default for .php files */
                if (empty($tpl) && strstr($fileName, '.php')) {
                    $tpl = $this->helpers->getTpl('phpfile.php');
                }
                $tpl = str_replace('[[+elementType]]', strtolower(substr($type,3)), $tpl);
                $tpl = str_replace('[[+elementName]]', $name, $tpl);
                if (!empty ($tpl)) {
                    $tpl = $this->helpers->replaceTags($tpl);
                }
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
            }
        }
    }

    /**
     * Creates a MODX element object in the DB if set in project config file
     *
     * @param $name string - name of object in MODX install
     * @param $type string - modSnippet, modChunk, etc.
     */
    public function createElementObject($name, $type) {
        /* @var $object modElement */
        $lName =strtolower($name);
        $alias = $type == 'modTemplate'? 'templatename' : 'name';
        $object = $this->modx->getObject($type, array($alias => $name));
        if (!$object) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Creating ' . $name . ' ' . $type . ' object in DB');
            $fields = array(
                $alias => $name,
                'category' => $this->categoryId,
            );
            /* Make it static and connect to file if requested */
            if  ($this->props['allStatic'] || in_array($name, $this->makeStatic)) {
                $fields['static'] = 1;
                $fields['source'] = 1;
                $fields['static_file'] = $this->helpers->getCodeDir($this->targetCore, $type) . '/' . $this->helpers->getFileName($name, $type);
            }
            $object = $this->modx->newObject($type, $fields);
            if ($object) {
                $object->save();
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Could not create ' . $type .  ' object: ' . $name);
            }
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $name . ' ' . $type . ' object already exists');
        }
    }


    /** creates resources in MODX install if set in project config file */
    public function createResources() {
        $res = $this->modx->getOption('resources', $this->props, '');
        $resources = !empty($res)? explode(',',$res) : array();
        if (! empty($resources)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating Resources');
        }
        foreach( $resources as $resource) {
            $res = $this->modx->getObject('modResource', array('pagetitle'=> $resource));
            if (! $res) {
                $alias = str_replace(' ', '-', strtolower($resource));
                $fields = array(
                    'pagetitle' => $resource,
                    'alias' => $alias,
                    'published' => $this->modx->getOption('publish_default', null),
                    'richtext' => $this->modx->getOption('richtext_default',null),
                    'hidemenu' => $this->modx->getOption('hidemenu_default', null),
                    'cacheable' => $this->modx->getOption('cache_default', null),
                    'searchable' => $this->modx->getOption('search_default', null),
                    'context' => $this->modx->getOption('default_context', null),
                    'template' => $this->modx->getOption('default_template', null),
                );
                /* @var $res modResource */
                $res = $this->modx->newObject('modDocument', $fields);
                if ($res) {
                    $res->setContent("Content goes here");
                    $res->save();
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    Created resource object ' . $resource);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, '    Could not create resource object ' . $resource);
                }
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Resource ' . $resource . ' object already exists');
            }
        }
    }
    /** Creates build transport and config files, (optionally) lexicon files, doc file,
     *  readme.md, and full _build directory with utilities if set in project config file */
    public function createBasics() {
        $defaults = $this->props['defaultStuff'];

        /* Transfer build and build config files */

        $dir = $this->targetBase . '_build';
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating build files');
        $fileName = 'build.transport.php';
        if (!file_exists($dir . '/' . $fileName)) {
            $tpl = $this->helpers->getTpl($fileName);
            $tpl = $this->helpers->replaceTags($tpl);
            $this->helpers->writeFile($dir, $fileName, $tpl);
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
        }
        $fileName = 'build.config.php';
        if (!file_exists($dir . '/' . $fileName)) {
            copy($this->source . '_build/build.config.php', $dir . '/' . 'build.config.php');
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');

        }
        if (isset ($defaults['utilities']) && $defaults['utilities']) {

            $fromDir = $this->source . '_build/utilities/';
            $toDir = $this->targetBase . '_build/utilities/';
            if (! is_dir($toDir)) {
                $this->modx->log(MODX::LOG_LEVEL_INFO, 'Copying Utilities directory');
                $this->helpers->copyDir($fromDir, $toDir);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Utilities directory already exists');
            }
        }

        if (isset ($this->props['languages']) &&  ! empty($this->props['languages'])) {
            $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating Lexicon files');
            $lexiconBase = $this->targetCore . 'lexicon/';
            foreach($this->props['languages'] as $language => $languageFiles) {
                $dir = $this->targetCore . 'lexicon/' . $language;
                $files = !empty($languageFiles)? explode(',', $languageFiles) : array();
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
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $language . ':' . $fileName . ' file already exists');
                    }

                }
            }
        }
        if (isset ($defaults['docs']) && ! empty($defaults['docs'])) {
            $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating doc files');
            $toDir = $this->targetCore . 'docs';
            $docs = !empty($defaults['docs'])? explode(',', $defaults['docs']) : array();
            foreach($docs as $doc) {
                if (! file_exists($toDir . '/' . $doc )) {
                    $tpl = $this->helpers->getTpl($doc);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($toDir, $doc, $tpl);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $doc . ' file already exists');
                }
            }
        }
        if (isset ($defaults['readme.md']) && $defaults['readme.md']) {
            if (! file_exists($this->targetBase . 'readme.md')) {
                $tpl = $this->helpers->getTpl('readme.md');
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($this->targetBase, 'readme.md', $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, 'readme.md file already exists');
            }

        }

        return true;
    }

    /** Creates assets directories and (optionally) empty css and js files
     * if set in project config file */
    public function createAssetsDirs() {
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
    /** Connects System Events to Plugins and creates resolver for connecting them
     *  during the build if set in the project config file */
    public function connectSystemEventsToPlugins() {

        $pluginEvents = $this->modx->getOption('pluginEvents', $this->props, array());
        $this->helpers->createIntersects($pluginEvents, 'modPluginEvent', 'modPlugin', 'modSystemEvent', 'pluginid', 'event');

        /* create the resolver */
        $pluginEvents = $this->modx->getOption('pluginEvents', $this->props, array());
        if (! empty($pluginEvents)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating plugin resolver');
            $tpl = $this->helpers->getTpl(('pluginresolver.php'));
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, 'pluginresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'plugin.resolver.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $code = '';
    
                $codeTpl = $this->helpers->getTpl('pluginresolvercode.php');
                if (empty($codeTpl)) {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, 'pluginresolvercode tpl is empty');
                }
                $codeTpl = str_replace('<' . '?' . 'php', '', $codeTpl);

                foreach($pluginEvents as $plugin => $events) {
                        $tempCodeTpl = str_replace('[[+plugin]]', $plugin, $codeTpl);
                        $tempCodeTpl = str_replace('[[+events]]', $events, $tempCodeTpl);
                        $code .= "\n" . $tempCodeTpl;
                }
                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $newEvents = $this->props['newSystemEvents'];
                $removeTpl = '';
                if (!empty($newEvents)) {
                    $removeTpl = $this->helpers->getTpl('removenewevents.php');
                    $removeTpl = str_replace('<' . '?' . 'php', '', $removeTpl);

                }
                $tpl = str_replace('/* [[+remove_new_events]] */', $removeTpl, $tpl);
                $tpl = str_replace('[[+category]]', $this->props['category'], $tpl);
                $tpl = str_replace('[[+newEvents]]', $newEvents, $tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }


        }
    }

    /** Connects TVs to templates and creates resolver connecting them in the package
     * if set in the project config file */
    public function connectTvsToTemplates()
    {
        $templateVarTemplates = $this->modx->getOption('templateVarTemplates', $this->props, array());
        $this->helpers->createIntersects($templateVarTemplates, 'modTemplateVarTemplate', 'modTemplate', 'modTemplateVar', 'templateid', 'tmplvarid');

        /* Create Resolver */
        if (!empty($templateVarTemplates)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating tv resolver');
            $tpl = $this->helpers->getTpl('tvresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, 'tvresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'tv.resolver.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->helpers->getTpl('tvresolvercode.php');
                $codeTpl = str_replace('<' . '?' . 'php', '', $codeTpl);

                foreach ($templateVarTemplates as $template => $tvs) {
                    $tempCodeTpl = str_replace('[[+template]]', $template, $codeTpl);
                    $tempCodeTpl = str_replace('[[+tvs]]', $tvs, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

            $tpl = str_replace('/* [[+code]] */', $code, $tpl);

            $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }

    /**
     * Connects Resources to package templates and creates a resolver to
     * connect them during the install.
     */
    public function connectResourcesToTemplates() {
        $data = $this->modx->getOption('resourceTemplates', $this->props, '');
        $this->helpers->createIntersects($data, 'resourceTemplates', 'modTemplate', 'modResource','','');
        /* Create resource.resolver.php resolver */
        if (!empty($data)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating resource resolver');
            $tpl = $this->helpers->getTpl('resourceresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, 'resourceresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'resource.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->helpers->getTpl('resourceresolvercode.php');
                $codeTpl = str_replace('<' . '?' . 'php', '', $codeTpl);

                foreach ($data as $template => $resources) {
                    $tempCodeTpl = str_replace('[[+template]]', $template, $codeTpl);
                    $tempCodeTpl = str_replace('[[+resources]]', $resources, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }
    /** Creates validators if set in project config file */
    public function createValidators() {
        $validators = $this->modx->getOption('validators', $this->props, '');
        if (!empty($validators)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating validators');
            $dir = $this->targetBase . '_build/validators';

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
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    '  . $fileName . ' already exists');
                }
            }
        }
    }
    /** Creates additional resolvers specified in project config file */
    public function createExtraResolvers() {
        $resolvers = $this->modx->getOption('resolvers', $this->props, '');
        if (!empty($resolvers)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating extra resolvers');
            $dir = $this->targetBase . '_build/resolvers';
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
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }
            }
        }
    }
    /** Creates new System Settings if set in project confic file */
    public function createNewSystemSettings() {
        $newSettings = $this->modx->getOption('newSystemSettings', $this->props, array());
        if (!empty($newSettings)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating New System Settings');
            foreach($newSettings as $key => $fieldValues) {
                $setting = $this->modx->getObject('modSystemSetting', array('key' => $key));
                if (!$setting) {
                    $setting = $this->modx->newObject('modSystemSetting');
                    /* @var $setting modSystemSetting */
                    if ($setting) {
                        $setting->set('key', $key);
                        $category = strtolower($this->props['category']);
                        $setting->set('area', $category);
                        $setting->set('namespace', $category);
                        foreach ($fieldValues as $fieldKey => $value) {
                            $setting->set($fieldKey, $value);
                        }
                        if ($setting->save()) {
                            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Created new system setting ' . $key);
                        } else {

                        }

                    }
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $key . ' System Setting already exists');


                }
            }

        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'No System Settings in config file');
        }


    }
    /** Creates example file for user input during install if set in project config file */
    public function createInstallOptions() {
        $iScript = $this->modx->getOption('install.options', $this->props, '');
        if (! empty($iScript)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating Install Options');
            $dir = $this->targetBase . '_build/install.options';
            $fileName = 'user.input.php';

            if (! file_exists($dir . '/' . $fileName)) {
                $tpl = $this->helpers->getTpl($fileName);
                $tpl = $this->helpers->replaceTags($tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
    }
    /** Creates propertyset objects in MODX install if set in project config file.
     * Create the property set's properties in the Manager and export them
     * with exportObjects */

    public function createPropertySets() {
        $propertySets = $this->modx->getOption('propertySets', $this->props, '' );
        if (! empty($propertySets)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating property sets');
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
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    Created ' . $name . ' property set object');
                    } else {
                        $this->modx->log(MODX::LOG_LEVEL_ERROR, '    Could not create ' . $name . ' property set object');
                    }

                } else {
                    /* do this in case the set is leftover from a bad install
                     * and has the wrong category ID (won't show in tree). */
                    if ($set->get('category') != $this->categoryId) {
                        $set->set('category', $this->categoryId);
                        $set->save();
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    Updated ' . $name . ' property set category');
                    } else {
                        $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $name . ' property set already exists');
                    }
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
            $this->helpers->createIntersects($propertySets,'modElementPropertySet','modPropertySet','modElement', 'property_set','element');
        }
        /* Create Resolver */
        if (!empty($propertySets)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating tv resolver');
            $tpl = $this->helpers->getTpl('propertysetresolver.php');
            $tpl = $this->helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, 'propertysetresolver tpl is empty');
            }
            $dir = $this->targetBase . '_build/resolvers';
            $fileName = 'propertyset.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $code = '';
                $codeTpl = $this->helpers->getTpl('propertysetresolvercode.php');
                $codeTpl = str_replace('<' . '?' . 'php', '', $codeTpl);

                foreach ($propertySets as $propertySet => $elements) {
                    $tempCodeTpl = str_replace('[[+propertySet]]', $propertySet, $codeTpl);
                    $tempCodeTpl = str_replace('[[+elements]]', $elements, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }

                $tpl = str_replace('/* [[+code]] */', $code, $tpl);

                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }

    }
    /** Creates "starter" class files specified in project config file */
    public function createClassFiles() {
        /* @var $element modElement */
        $classes = $this->modx->getOption('classes', $this->props, array());
        $classes = !empty($classes) ? $classes : array();
        if (!empty($classes)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating class files');
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
                    $tpl = $this->helpers->getTpl('classfile.php');
                    $tpl = str_replace('MyClass', $className, $tpl );
                    $tpl = str_replace('[[+className]]', $className, $tpl);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($dir, $fileName, $tpl);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' file already exists');
                }

            }
        }


    }

    /* The next three function are not used, but can replace placeholders in files after the fact */
    /* ********************************************************************* */
    public function doSearchReplace() {
        $this->_doSearchReplace($this->dest);
    }

    protected function _doSearchReplace($path, &$name = array() ) {

        $names = array();
    
        $path = $path == ''? dirname(__FILE__) : $path;
        $lists = @scandir($path);
    
        if(!empty($lists)){
          foreach($lists as $f) {
              if(is_dir($path. '/' . $f)) {
                if ($f == ".." || $f == "." || strstr ($f,'.git' )) {
                    continue;
                }
                $this->_doSearchReplace($path. '/'. $f, $name);
              } else {
                  if (! $this->ignore($f) ) {
                      $names[] = $path. '/' . $f;
                      $this->modx->log(MODX::LOG_LEVEL_INFO,'Processing: ' . $path . '/' . $f);
                  } else {
                      $this->modx->log(MODX::LOG_LEVEL_INFO,'----Ignoring: ' . $path . '/' . $f);
                  }
    
              }
          }
        }
        return $names;
    }
    protected function ignore($f) {
           /* make sure all sample files get transferred */
           if (strstr($f, 'sample')) return false;

           /* skip build.config.php */
           if ($f == 'build.config.php') return true;

           /* skip all project config files except the one for this project */
           if (strstr($f, 'config.php') && $f != PKG_NAME_LOWER . '.config.php') return true;

           return false;
    }

} /* end of class */
