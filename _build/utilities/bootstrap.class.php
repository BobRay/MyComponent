<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 4/19/12
 * Time: 11:50 PM
 * To change this template use File | Settings | File Templates.
 */




class Bootstrap {
    /* @var $modx modX - MODX object */
    var $modx;
    /* @var $props array  - $scriptProperties array */
    var $props;
    /* @var $helpers Helpers  - class of helper functions */
    var $helpers;
    var $packageName;
    var $packageNameLower;
    var $source;
    var $targetBase;
    var $targetCore;
    var $targetAssets;
    var $corePath;
    var $assetsPath;
    var $tplPath; /* path to element Tpl files */
    var $categoryId;
    var $makeStatic; /* array of objects to make static (comma,separated list in config) */
    var $dirPermission;
    var $filePermission;



    function  __construct(&$modx, &$props = array()) {
                $this->modx =& $modx;
                $this->props =& $props;
    }


    public function init() {
        clearstatcache(); /*  make sure is_dir() is current */
        $config = dirname(dirname(__FILE__)) . '/build.config.php';
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
        require_once $this->source . '_build/utilities/helpers.class.php';
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

        $this->makeStatic = explode(',', $this->props['makeStatic']);

        /* show basic info */
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Component: ' . $this->props['packageName']);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Source: ' . $this->source);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Base: ' . $this->targetBase);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Core: ' . $this->targetCore);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Assets: ' . $this->targetAssets);

        $this->modx->log(MODX::LOG_LEVEL_INFO, '--------------------------------------------------');



    }
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

    public function createElements() {
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Category ID: ' . $this->categoryId);

        foreach ($this->props['elements'] as $elementType => $elements) {
            $elements = explode(',', $elements);
            foreach ($elements as $name) {
                if (! empty ($name)) {
                    $this->createElement($name, $elementType);
                }
            }
        }
    }

    /**
     * Creates an element (code file and or MODX object) based on config file
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
     * Creates a code file for an element
     *
     * @param $name string - lowercase filename (without extension or type
     * @param $codeDir string - directory for element file (must not end in a slash)
     * @param $type string - plugin, snippet, css, js, etc.
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
     * Creates a MODX element object in the DB
     *
     * @param $name
     * @param $type
     * @param $suffix
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
    public function createResources() {
        $resources = explode(',', $this->props['resources']);
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

        if (isset ($defaults['lexicon']) && $defaults['lexicon']) {
            $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating Lexicon files');
            $toDir = $this->targetCore . 'lexicon';
            //echo  "\n" . 'TODIR: ' . $toDir . "\n";
            if (! is_dir($toDir)) {
                $this->modx->log(MODX::LOG_LEVEL_INFO,'    Creating lexicon directory');
                mkdir($toDir, $this->dirPermission, true);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO,'    Lexicon directory already exists');
            }
            if (!empty($defaults['languages'])) {
                $languages = explode(',', $defaults['languages']);
                foreach($languages as $lang) {
                    if (!is_dir($toDir . '/' . $lang)) {
                        $this->modx->log(MODX::LOG_LEVEL_INFO,'        creating ' . $lang . ' directory');
                        mkdir($toDir . '/' . $lang, $this->dirPermission, true);
                    } else {
                        $this->modx->log(MODX::LOG_LEVEL_INFO,'        ' . $lang . ' directory already exists');
                    }
                }
            }
        }
        if (isset ($defaults['docs']) && ! empty($defaults['docs'])) {
            $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating doc files');
            $toDir = $this->targetCore . 'docs';
            $docs = explode(',', $defaults['docs']);
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
    public function createAssetsDirs() {
        $optionalDirs = $this->props['assetsDirs'];
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
                if (!file_exists($dir . '/' . $fileName)) {
                    $tpl = $this->helpers->getTpl($dir);
                    $tpl = $this->helpers->replaceTags($tpl);
                    $this->helpers->writeFile($path, $fileName, $tpl);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
                }

            }
        }


}
    /** creates resolver for attaching events to plugins */
    public function createPluginResolver() {
        $pluginEvents = $this->props['pluginEvents'];
        if (! empty($pluginEvents)) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating plugin resolver');
            $tpl = $this->helpers->getTpl(('pluginresolver.php'));
            $this->helpers->replaceTags($tpl);
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
                $codeTpl = str_replace('<?php', '', $codeTpl);
    
                foreach($pluginEvents as $plugin => $events) {
    
                    $tempCodeTpl = str_replace('[[+plugin]]', $plugin, $codeTpl);
                    $tempCodeTpl = str_replace('[[+events]]', $events, $tempCodeTpl);
                    $code .= "\n" . $tempCodeTpl;
                }
                $tpl = str_replace('/* [[+code]] */', $code, $tpl);
                $this->helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }


        }
    }

    /** creates resolver for attaching TVs to Templates */
    public function createTvResolver()
    {
        $templateVarTemplates = $this->props['templateVarTemplates'];
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
                $codeTpl = str_replace('<?php', '', $codeTpl);

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
    public function createValidators() {
        $validators = $this->props['validators'];
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
    public function createExtraResolvers() {
        $resolvers = $this->props['resolvers'];
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
    public function createInstallOptions() {
        $iScript = $this->props['install.options'];
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

    /* The next three function are not used, but can replace placeholders in files after the fact */
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
