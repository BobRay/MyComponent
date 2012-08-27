<?php
/**
 * CheckProperties Utility Script for My Component
 * @author Bob Ray
 * Copyright 2012 Bob Ray
 * Modified: July, 2012
 *
 * CheckProperties is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * CheckProperties is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CheckProperties; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Bob Ray <http://bobsguides.com>

 *
 * Description: The CheckProperties script identifies properties
 * used in code with $modx->getOption() or some version of $scriptProperties and checks 
 * them against properties in the properties file.
 *
 * Output can be pasted into the properties file.
 *
 * No files are altered.
 */


if (!defined('MODX_CORE_PATH')) {
    /* no $modx object, just getting the paths */
    require_once dirname(dirname(__FILE__)) . '/build.config.php';
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
}
if (!php_sapi_name() == 'cli') {
    echo "<pre>\n"; /* used for nice formatting for $output  */
}
require_once MODX_ASSETS_PATH . 'mycomponents/mycomponent/_build/utilities/checkProperties.class.php';

$checkProperties = new CheckProperties();
$checkProperties->init(dirname(dirname(__FILE__)) . '/build.config.php');
$checkProperties->run();


