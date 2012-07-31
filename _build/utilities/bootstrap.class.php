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

    var $packageName;
    var $packageNameLower;
    var $config;
    var $source;
    var $sourceCore;
    var $targetBase;
    var $targetCore;
    var $targetAssets;
    var $corePath;
    var $assetsPath;
    var $tplPath; /* path to element Tpl files */
    var $replaceFields; /* replacements for placeholders in element tpl files */
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
        $configFile = include 'bootstrap.config.php';
        $this->config = '';
        $this->config = @include $configFile;
        if (empty($this->config)) {
            die('Could not find config file');
        }
        $this->source = $this->config['source'];

        $this->packageName = $this->config['packageName'];
        $this->packageNameLower = $this->config['packageNameLower'];



        if (empty($this->config)) {
            die('Config file not found: ' . $configFile);
        }
        unset($configFile);

        if (isset($this->config['offerAbort']) && $this->config['offerAbort']) {
            echo 'Processing ' . $this->packageName . 'Continue? (y/n - Enter) ';
            $input = fgetc(STDIN);
            if ($input != 'y' && $input != 'Y') {
                die ('Operation aborted');
            }
        }

        $this->targetBase = MODX_BASE_PATH . 'assets/mycomponents/' . $this->packageNameLower . '/';
        $this->targetCore = $this->targetBase . 'core/components/' . $this->packageNameLower . '/';
        $this->sourceCore = $this->source . 'core/components/mycomponent/';
        $this->targetAssets = $this->targetBase . 'assets/components/'. $this->packageNameLower . '/';

        $this->tplPath = $this->source .  '/_build/utilities/buildtpls/' .'/';

        $this->replaceFields = array(
            '[[+packageName]]' => $this->config['packageName'],
            '[[+packageNameLower]]' => $this->config['packageNameLower'],
            '[[+author]]' => $this->config['author'],
            '[[+email]]' => $this->config['email'],
            '[[+copyright]]' => $this->config['copyright'],
            '[[+createdon]]' => $this->config['createdon'],
        );

        $this->dirPermission = $this->config['dirPermission'];
        $this->filePermission = $this->config['filePermission'];

        $this->makeStatic = explode(',', $this->config['makeStatic']);

        /* show basic info */
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Component: ' . $this->config['packageName']);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Base: ' . $this->targetBase);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Target Core: ' . $this->targetCore);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Source: ' . $this->source);
        $this->modx->log(MODX::LOG_LEVEL_INFO, 'SourceCore: ' . $this->sourceCore);
        $this->modx->log(MODX::LOG_LEVEL_INFO, '--------------------------------------------------');



    }
    public function createCategory() {

        /* @var $category modCategory */
        $category = $this->modx->getObject('modCategory', array('category' => $this->packageName));
        if (! $category) {
            $category = $this->modx->newObject('modCategory', array('category' => $this->packageName));
            $category->save();
            $this->categoryId = $category->get('id');
        } else {
            $this->categoryId = $category->get('id');
        }
        unset($category);
    }

    public function createElements() {
        //$this->modx->log(MODX::LOG_LEVEL_INFO, 'Category ID: ' . $this->categoryId);



        foreach ($this->config['elements'] as $elementName => $elements) {
            $elements = explode(',', $elements);
            foreach ($elements as $name) {
                if (! empty ($name)) {
                    $this->createElement($name, $elementName);
                }



            }
        }
    }

    /**
     * Creates an element (code file and or MODX object) based on config file
     *
     * @param $name string - Name of Element
     * @param $type - Plural element type (e.g. 'plugins')
     */
    public function createElement ($name, $type) {

        // echo "\nNAME: " . $name .  "\nTYPE: " . $type;
        $lName = strToLower($name);
        /* fileNameType is type without the final s */
        $fileNameType = substr(strtolower($type),0,-1);
        $suffix = $this->config['suffixes'][$fileNameType];

        //echo "\nDIRNAME: " . $fileNameType;

        $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating ' . $name . ' ' . $fileNameType);

        if ($this->config['createElementFiles']) {
            $codeDir = $this->targetCore . 'elements/' . $type;
            // echo "\nCODE DIR: " . $codeDir;
            if (! is_dir($codeDir)) {
                mkdir($codeDir, $this->dirPermission, true);
            }
            $codePath = $codeDir . '/' . $lName . '.' . $fileNameType . $suffix;
            $this->createCodeFile($name, $codePath, $fileNameType);
            // echo "\nCODE_PATH: " . $codePath . "\n";
        }
        if ($this->config['createElementObjects']) {
            $this->createElementObject($name, $fileNameType, $suffix);
        }

    }

    /**
     * Creates a code file for an element
     *
     * @param $name string - filename (without extension or type - usually $this->packageNameLower)
     * @param $codePath string - full path to file including filename
     * @param $type string - plugin, snippet, css, js, etc.
     */
    public function createCodeFile($name, $codePath, $type) {

        $tpl = $this->getTpl($type);

        /* use 'phpfile.tpl' as default for .php files */
        if ( empty($tpl) && strstr($codePath, 'php')) {
            $tpl = $this->getTpl('phpfile');
        }

        /* add license if necessary */
        if (! empty($tpl) && strstr($tpl, '[[+license]]')) {
            $license = $this->getTpl('license');
            $tpl = str_replace('[[+license]]', $license, $tpl);
        }
        $fp = null;
        if (! file_exists($codePath)) {
            $fp = fopen($codePath, 'w');
            if ($fp) {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Creating ' . $name . ' ' . $type . ' file');
                $replace = $this->replaceFields;
                $replace['[[+elementType]]'] = ucfirst($type);
                $replace['[[+elementName]]'] = $name;
                $fileContent = $tpl;
                if (!empty ($tpl)) {
                    $fileContent = $this->strReplaceAssoc($replace, $fileContent);
                }
                fwrite($fp,$fileContent);
                fclose($fp);
                chmod($codePath, $this->filePermission);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, '    Could not write code file ' . $codePath);
            }
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $codePath . ' already exists');
        }

    }

    /**
     * Creates a MODX element object in the DB
     *
     * @param $name
     * @param $type
     * @param $suffix
     */
    public function createElementObject($name, $type, $suffix) {
        /* @var $object modElement */
        $lName =strtolower($name);
        /* $objectType is 'modPlugin', 'modChunk', etc. */
        $objectType = $type == 'tv' ? 'modTemplateVar' : 'mod' . ucfirst($type);
        $alias = $type == 'templatename'? 'template' : 'name';
        $object = $this->modx->getObject($objectType, array($alias => $name));
        if (!$object) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    Creating ' . $name . ' ' . $type . ' object in DB');
            $fields = array(
                $alias => $name,
                'category' => $this->categoryId,
            );
            /* Make it static and connect to file if requested */
            if  ($this->config['allStatic'] || in_array($name, $this->makeStatic)) {

                $fields['static'] = 1;
                $fields['source'] = 1;
                $fields['static_file'] = 'assets/mycomponents/' . $this->packageNameLower  . '/core/components/' . $this->packageNameLower .  '/elements/'  . $type . 's/' . $lName . "." . $type . $suffix;
            }
            $object = $this->modx->newObject('modPlugin', $fields);
            if ($object) {
                $object->save();
            }
        } else {
            $this->modx->log(MODX::LOG_LEVEL_INFO, '    ' . $name . ' ' . $type . ' object already exists');
        }
    }

    public function createBasics() {
        $defaults = $this->config['defaultStuff'];
        $source = $this->source;
        $target = $this->targetBase;
        $core = $this->targetCore;
        $assets = $this->targetAssets;

        if (isset ($defaults['_build']) && $defaults['_build']) {
            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating directory: ' . $this->targetBase);
            // mkdir($this->targetBase, $this->dirPermission, true);
            // mkdir($this->targetBase . '_build', $this->dirPermission, true);
            if (! is_dir($this->targetBase . '_build/data')) {
                $this->modx->log(MODX::LOG_LEVEL_INFO, 'Creating directory: ' . $this->targetBase);
                mkdir($this->targetBase . '_build/data', $this->dirPermission, true);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO, 'Directory already exists: ' . $this->targetBase);
            }
            $fromDir = $this->source . '_build/';
            $toDir = $this->targetBase . '_build/';
            $files = array(
                'build.config.sample.php',
                'build.config.php',
                'build.transport.php',
            );
            foreach ($files as $file) {
                if (! file_exists($toDir . $file)) {
                    copy ($fromDir . $file, $toDir . $file );
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_INFO, '    File already exists: ' . $file);
                }
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
        if (isset ($defaults['docs']) && $defaults['docs']) {
            $this->modx->log(MODX::LOG_LEVEL_INFO,'Creating doc files');
            $fromDir = $this->sourceCore . 'docs';
            $toDir = $this->targetCore . 'docs';
            if (! is_dir($toDir)) {
                mkdir($toDir, $this->dirPermission, true);
                $this->modx->log(MODX::LOG_LEVEL_INFO,'    copying doc files');
                $this->_copy($fromDir,$toDir);
            } else {
                $this->modx->log(MODX::LOG_LEVEL_INFO,'    docs directory already exists -- no files copied');
            }

        }

        return true;
    }
    public function createAssetsDirs() {
        $optionalDirs = $this->config['assetsDirs'];
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
                $file = $this->packageNameLower . '.' . $dir;
                $this->createCodeFile($file , $targetDir . '/' . $file, $dir);
            }
        }


}

protected function getTpl($name) {
    $text = @file_get_contents($this->tplPath . $name . '.tpl');
    return $text !== false? $text : '';
}
    /**
     * Copies an entire directory and its descendants 
     * 
     * @param $source
     * @param $destination
     * @return bool
     */
    protected function _copy( $source, $destination) {
        //echo "SOURCE: " . $source . "\nDESTINATION: " . $destination . "\n";
        if( is_dir($source) ) {
            if (! is_dir($destination)) {
                mkdir( $destination, $this->dirPermission, true);
            }
            $objects = scandir($source);
            if( sizeof($objects) > 0 ) {
                foreach( $objects as $file ) {
                    if( $file == "." || $file == ".." || $file == '.git' || $file == '.svn') {
                        continue;
                    }

                    if(is_dir( $source. '/' . $file ) ) {
                        $this->_copy( $source. '/'. $file, $destination. '/' .$file );
                    } else {
                        copy( $source. '/' . $file, $destination. '/' . $file );
                    }
                }
            }
            return true;
        }
        elseif( is_file($source) ) {
            return copy($source, $destination);
        } else {
            return false;
        }
    }
    function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
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
           /* $noProcess = array_merge(array(
                '.gitignore',
                '.zip',
                '.html',
                '.js',
                '.css',
                '.tpl',
                '.gif',
                '.jpg',
                '.wav',
                '.mov',
                '.mpg',
            ),$this->noProcess);*/
    
            foreach ($this->noProcess as $s) {
                if (stristr($f,$s)) {
                    return true;
                }
            }
            return false;
    }

} /* end of class */
