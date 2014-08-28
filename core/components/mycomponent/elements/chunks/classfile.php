<?php
/**
 * [[+className]] class file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 */


class MyClass {
    /** @var $modx modX */
    public $modx;
    /** @var $props array */
    public $props;

    function __construct(&$modx, &$config = array()) {
        $this->modx =& $modx;
        $this->props =& $config;
    }

    /* [[+code]] */

}