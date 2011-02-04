<?php
/**
 * MyComponent
 * Copyright 2011 Your Name <you@yourdomain.com>
 *
 * MyComponent is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Your Name <you@yourdomain.com>
 *
 * @version Version 1.0.0 Beta-1
 * 1/1/11
 *
 * Description
 *

/**
  @version Version 1.0.0 Beta-1

 /** Example properties
 * &package mycomponent
 *
 *  Required Properties:
 *    @property property1 - (boolean) Description; default value.
 *
 *  Optional properties:
 *    @property property1 - (string) Description; default value.
 */

/* Example of how to use a system setting to load a class file

require_once $modx->getOption('np.core_path', null, $modx->getOption('core_path') . 'components/mycomponent/') . 'classes/mycomponent.class.php';

*/

/* Your snippet code here */

/* This example calculates the action page for the cheatsheet,
 * which appears in the page links of the two chunks.
 */

$action = $modx->getObject('modAction', array('namespace'=>'mycomponent'));
return ($action->get('id'));

?>
