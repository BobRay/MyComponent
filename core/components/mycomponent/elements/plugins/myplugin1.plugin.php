<?php
/**
 * Mycomponent myplugin1
 *
 * Copyright 2011 Your Name <you@yourdomain.com>
 *
 * @author Your Name <you@yourdomain.com>
 * @created 09/23/2008
 * @version Version 1.0.0 Beta-1
 * 1/17/11
 *
 * Mycomponent is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * Mycomponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Mycomponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */

/**
 * MODx Mycomponent myplugin1
 *
 * Description: Mycomponent plugin for MODx login verification
 * Events: OnBeforeManagerLogin, OnManagerLoginFormRender
 *
 * Important Note: If OnBeforeManagerLogin doesn't set
 * $modx->event->_output to true, the login will fail.
 *
 * @package mycomponent
 *
 * @property
 */

/* only do this if you need lexicon strings */
$modx->lexicon->load('mycomponent:default');

/* These are examples */

switch ($modx->event->name) {
    case 'OnBeforeManagerLogin':
        $rt = true;
        /* do some stuff */
        $modx->event->_output = $rt;
        break;

    case 'OnManagerLoginFormRender': 
        $rt = '';
        /* do some other stuff (e.g., add stuff to login form) */
        $modx->event->_output = $rt;
        
    break;
}