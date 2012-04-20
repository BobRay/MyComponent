<?php

$props =& $scriptProperties;

/* @var $modx modX */
if (!defined(MODX_CORE_PATH)) {
    require_once dirname(dirname(__FILE__)).'/build.config.php';
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx= new modX();
    $modx->initialize('mgr');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');
    echo '<pre>'; /* used for nice formatting for log messages  */
}
    define('DS', DIRECTORY_SEPARATOR);
    $scriptProperties = array(
      'ignoreDirs' => array(),
      'noProcess' => array(),
      'componentName' => 'test2',
    );
    $props =& $scriptProperties;
    require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/bootstrap.class.php';
    $bootStrap = new Bootstrap($modx,$props);
    $bootStrap->init();
    $componentName = $props['componentName'];

    /*$source = MODX_ASSETS_PATH . 'mycomponents/mycomponent';
    $dest = MODX_ASSETS_PATH . 'mycomponents/' . $componentName;*/

    if (! $bootStrap->copy()) {
        die();
    }

    /*$old = MODX_ASSETS_PATH . 'mycomponents/' . $componentName . '/assets/components/mycomponent';
    $new = MODX_ASSETS_PATH . 'mycomponents/' . $componentName . '/assets/components/' . $componentName;*/
    $bootStrap->renameDirs();

    /*$old = MODX_ASSETS_PATH . 'mycomponents/' . $componentName . '/core/components/mycomponent';
    $new = MODX_ASSETS_PATH . 'mycomponents/' . $componentName . '/core/components/' . $componentName;*/
    $bootStrap->renameDirs();

    $modx->log(MODX::LOG_LEVEL_INFO,'Finished renaming. Doing Search and Replace');

    $bootStrap->doSearchReplace();
    $modx->log(MODX::LOG_LEVEL_INFO,'Finished!');


    /*function copy_r( $source, $dest) {
        if( is_dir($source) ) {
            @mkdir( $dest );
            $objects = scandir($source);
            if( sizeof($objects) > 0 ) {
                foreach( $objects as $file ) {
                    if( $file == "." || $file == ".." || $file == '.git' || $file == '.svn') {
                        continue;
                    }
                    // go on
                    if( is_dir( $source.DS.$file ) ) {
                        copy_r( $source.DS.$file, $dest.DS.$file );
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

function doSearchReplace($path = '', &$name = array() ) {
    global $modx;
    $names = array();

    $path = $path == ''? dirname(__FILE__) : $path;
    $lists = @scandir($path);

    if(!empty($lists)){
      foreach($lists as $f) {
          if(is_dir($path.DS.$f)) {
            if ($f == ".." || $f == "." || strstr ($f,'.git' )) {
                continue;
            }
            doSearchReplace($path.DS.$f, &$name);
          } else {
              if (! ignore($f) ) {
                  $names[] = $path.DS.$f;
                  $modx->log(MODX::LOG_LEVEL_INFO,'Processing: ' . $path .DS.$f);
              } else {
                  $modx->log(MODX::LOG_LEVEL_INFO,'----Ignoring: ' . $path .DS.$f);
              }

          }
      }
    }
    return $names;
}
function ignore($f) {
        $noProcess = array(
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
        );

        foreach ($noProcess as $s) {
            if (stristr($f,$s)) {
                return true;
            }
        }
        return false;
}

    function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
    }*/

?>