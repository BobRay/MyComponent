<?php
/**
 * Request handler for mc_packageName extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package mc_packageNameLower
 */
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
/**
 * Encapsulates the interaction of MODx manager with an HTTP request.
 *
 * @package mc_packageNameLower
 * @extends modRequest
 */
class mc_packageNameControllerRequest extends modRequest {
    public $mc_packageNameLower = null;
    public $actionVar = 'action';
    public $defaultAction = 'home';

    function __construct(mc_packageName &$mc_packageNameLower) {
        parent :: __construct($mc_packageNameLower->modx);
        $this->mc_packageNameLower =& $mc_packageNameLower;
    }

    /**
     * Extends modRequest::handleRequest and loads the proper error handler and
     * actionVar value.
     *
     */

    public function handleRequest() {
        $this->loadErrorHandler();

        /* save page to manager object. allow custom actionVar choice for extending classes. */
        $this->action = isset($_REQUEST[$this->actionVar]) ? $_REQUEST[$this->actionVar] : $this->defaultAction;

        return $this->_respond();
    }

    /**
     * Prepares the MODx response to a mgr request that is being handled.
     * 
     */
    private function _respond() {
        $modx =& $this->modx;
        $mc_packageNameLower =& $this->mc_packageNameLower;
        $viewHeader = include $this->mc_packageNameLower->config['corePath'].'controllers/mgr/header.php';

        $f = $this->mc_packageNameLower->config['corePath'].'controllers/mgr/'.$this->action.'.php';
        if (file_exists($f)) {
            $viewOutput = include $f;
        } else {
            $viewOutput = 'Action not found: '.$f;
        }

        return $viewHeader.$viewOutput;
    }
}