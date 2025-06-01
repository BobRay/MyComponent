<?php
/**
 * Controller file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage controllers
 */
/* @var $modx modX */

$v = include MODX_CORE_PATH . 'docs/version.inc.php';
$isMODX3 = $v['version'] >= 3;

/* Note: controller_parent is *not* the controller's parent.
   It's a local class name used only once to extend
   this controller class */
if ($isMODX3) {
    abstract class controller_parent extends MODX\Revolution\modManagerController {
    }
} else {
    if (! class_exists('modManagerController')) {
        $includeFile = MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';

        if (file_exists($includeFile)) {
            include $includeFile;
        } else {
            die('Could not find parent class file: ' . $includeFile);
        }
    }

    abstract class controller_parent extends modManagerController {
    }
}

class ExampleHomeManagerController extends controller_parent {
    /**
     * The pagetitle to put in the <title> attribute.
     *
     * @return null|string
     */

    public function getPageTitle() {
        return $this->modx->lexicon('mc_packageName');
    }

    /* Must Return true */
    public function initialize() {
        /* Instantiate the Example class in the controller */
        $path = $this->modx->getOption('example.core_path',
                NULL, $this->modx->getOption('core_path') .
                'components/example/') . 'model/example/';
        require_once $path . 'example.class.php';
        $this->example = new Example($this->modx);

        /* Optional alternative  - install PHP class as a service */

        /* $this->example = $this->modx->getService('example',
             'Example', $path);*/

        /* Add the main javascript class and our configuration */
        $this->addJavascript($this->example->config['jsUrl'] .
            'example.class.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Example.config = ' . $this->modx->toJSON($this->example->config) . ';
        });
        </script>');
        return true;
    }

    /* The next three methods are required */

    public function checkPermissions() {
        return true;
    }
    public function getTemplateFile() {
        return ('../../templates/mgr.tpl');
    }

    /* Argument is required in PHP 8 */
    public function process($scriptProperties = array()) {

    }

    /**
     * Register all the needed javascript files.
     */

    public function loadCustomCssJs() {
        $this->addJavascript($this->mc_packageNameLower->config['jsUrl'] . 'widgets/chunk.grid.js');
        $this->addJavascript($this->mc_packageNameLower->config['jsUrl'] . 'widgets/snippet.grid.js');
        $this->addJavascript($this->mc_packageNameLower->config['jsUrl'] . 'widgets/home.panel.js');
        $this->addLastJavascript($this->mc_packageNameLower->config['jsUrl'] . 'sections/home.js');

        $this->addCss($this->mc_packageNameLower->config['cssUrl'] . 'mgr.css');
    }
}
