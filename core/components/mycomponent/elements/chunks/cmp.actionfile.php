<?php
/**
* Action file for [[+packageName]] extra
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
*
* @package [[+packageNameLower]]
*/


abstract class mc_packageNameManagerController extends modExtraManagerController {
    /** @var mc_packageName $mc_packageNameLower */
    public $mc_packageNameLower = NULL;

    /**
     * Initializes the main manager controller.
     */
    public function initialize() {
        /* Instantiate the mc_packageName class in the controller */
        $path = $this->modx->getOption('mc_packageNameLower.core_path',
                NULL, $this->modx->getOption('core_path') .
                'components/mc_packageNameLower/') . 'model/mc_packageNameLower/';
        require_once $path . 'mc_packageNameLower.class.php';
        $this->mc_packageNameLower = new mc_packageName($this->modx);

        /* Optional alternative  - install PHP class as a service */

        /* $this->mc_packageNameLower = $this->modx->getService('mc_packageNameLower',
             'mc_packageName', $path);*/

        /* Add the main javascript class and our configuration */
        $this->addJavascript($this->mc_packageNameLower->config['jsUrl'] .
            'mc_packageNameLower.class.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            mc_packageName.config = ' . $this->modx->toJSON($this->mc_packageNameLower->config) . ';
        });
        </script>');
    }

    /**
     * Defines the lexicon topics to load in our controller.
     *
     * @return array
     */
    public function getLanguageTopics() {
        return array('mc_packageNameLower:default');
    }

    /**
     * We can use this to check if the user has permission to see this
     * controller. We'll apply this in the admin section.
     *
     * @return bool
     */
    public function checkPermissions() {
        return true;
    }

    /**
     * The name for the template file to load.
     *
     * @return string
     */
    public function getTemplateFile() {
        return dirname(__FILE__) . '/templates/mgr.tpl';
        // return $this->mc_packageNameLower->config['templatesPath'] . 'mgr.tpl';
    }
}

/**
 * The Index Manager Controller is the default one that gets called when no
 * action is present.
 */
class IndexManagerController extends mc_packageNameManagerController {
    /**
     * Defines the name or path to the default controller to load.
     *
     * @return string
     */
    public static function getDefaultController() {
        return 'home';
    }
}
