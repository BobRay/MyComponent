<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bob Ray
 * Date: 4/19/12
 * Time: 11:50 PM
 * To change this template use File | Settings | File Templates.
 */

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}



class Bootstrap {
    /* @var $modx modX - MODX object */
    var $modx;
    /* @var $props array  - $scriptProperties array */
    var $props;
    /* @var $noProcess array - extensions to skip in search/replace */
    var $noProcess;
    /* @var $ignoreDirs array - directories to ignore */
    var $ignoreDirs;
    var $source;
    var $dest;
    var $componentName;
    var $componentNameLower;

    function  __construct(&$modx, &$props = array()) {
                $this->modx =& $modx;
                $this->props =& $props;
    }

    public function renameDirs() {
        $old = MODX_ASSETS_PATH . 'mycomponents/' . $this->componentNameLower . '/assets/components/mycomponent';
        $new = MODX_ASSETS_PATH . 'mycomponents/' . $this->componentNameLower . '/assets/components/' . $this->componentName;
        @rename($old,$new);
        $old = MODX_ASSETS_PATH . 'mycomponents/' . $this->componentNameLower . '/core/components/mycomponent';
        $new = MODX_ASSETS_PATH . 'mycomponents/' . $this->componentNameLower . '/core/components/' . $this->componentName;
        @rename($old,$new);

    }
    public function init() {
        $this->ignoreDirs = array_merge(array('.','..','.git','.svn'), $this->props['ignoreDirs']);
        $this->noProcess = array_merge(array('.gitignore','.zip','.html','.js','.css',
                '.tpl','.gif','.jpg','.wav','.mov','.mpg',),$this->props['noProcess']);
        $this->componentName = $this->props['componentName'];
        $this->componentNameLower = strtolower($this->componentName);
        $this->source = MODX_ASSETS_PATH . 'mycomponents/mycomponent';
        $this->dest = MODX_ASSETS_PATH . 'mycomponents/' . $this->componentNameLower;

    }
    public function copy() {
        $this->modx->log(MODX::LOG_LEVEL_INFO,'Copying ' . $this->source. ' to ' . $this->dest);
         if (! $this->_copy($this->source,$this->dest)) {
             $this->modx->log(MODX::LOG_LEVEL_ERROR,'Failed to find directory: ' . $this->source);
             return false;
         };
         return true;
    }
    protected function _copy( $source, $dest) {
            if( is_dir($source) ) {
                @mkdir( $dest );
                $objects = scandir($source);
                if( sizeof($objects) > 0 ) {
                    foreach( $objects as $file ) {
                        if (in_array($file,$this->ignoreDirs)) {
                            continue;
                        }
                        /*if( $file == "." || $file == ".." || $file == '.git' || $file == '.svn') {
                            continue;
                        }*/

                        if( is_dir( $source.DS.$file ) ) {
                            $this->_copy( $source.DS.$file, $dest.DS.$file );
                        } else {
                            copy( $source.DS.$file, $dest.DS.$file );
                        }
                    }
                }
                return true;
            }
            elseif( is_file($source) ) {
                return copy($source, $dest);
            } else {
                return false;
            }
        }
    public function doSearchReplace() {
        $this->_doSearchReplace($this->dest);
    }
    protected function _doSearchReplace($path, &$name = array() ) {

        $names = array();
    
        $path = $path == ''? dirname(__FILE__) : $path;
        $lists = @scandir($path);
    
        if(!empty($lists)){
          foreach($lists as $f) {
              if(is_dir($path.DS.$f)) {
                if ($f == ".." || $f == "." || strstr ($f,'.git' )) {
                    continue;
                }
                $this->_doSearchReplace($path.DS.$f, $name);
              } else {
                  if (! $this->ignore($f) ) {
                      $names[] = $path.DS.$f;
                      $this->modx->log(MODX::LOG_LEVEL_INFO,'Processing: ' . $path .DS.$f);
                  } else {
                      $this->modx->log(MODX::LOG_LEVEL_INFO,'----Ignoring: ' . $path .DS.$f);
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
    
        function strReplaceAssoc(array $replace, $subject) {
           return str_replace(array_keys($replace), array_values($replace), $subject);
        }
    

}
