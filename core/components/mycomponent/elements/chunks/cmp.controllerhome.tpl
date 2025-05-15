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
    $includeFile = MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
    if (file_exists($includeFile)) {
        include $includeFile;
    } else {
        return "Include File does not exist";
    }
    abstract class controller_parent extends modManagerController {
    }
}

class mc_controller_name extends controller_parent {
    /**
     * The pagetitle to put in the <title> attribute.
     *
     * @return null|string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('mc_packageNameLower');
    }

    /* The next three methods are required,
       even if you dont use them.
    */
    public function checkPermissions() {
    }
    public function getTemplateFile() {
    }
    public function process() {
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
