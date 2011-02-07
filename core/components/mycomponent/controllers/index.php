<?php

/**
 * Controller index.php for the MyComponent package
 * @author Your Name
 * 2/4/11
 *
 * @package mycomponent

 */

/* This file is not used in the package. It's an example of a possible controller */

require_once dirname(dirname(__FILE__)).'/model/mycomponent/mycomponent.class.php';
$mycomponent = new MyComponent($modx, $scriptProperties);
return $mycomponent->init('mgr');
