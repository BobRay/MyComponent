<?php
/**
 * Controller file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage controllers
 */
/* @var $modx modX */

class mc_packageNameHomeManagerController extends mc_packageNameManagerController {
    /**
     * The pagetitle to put in the <title> attribute.
     *
     * @return null|string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('mc_packageNameLower');
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