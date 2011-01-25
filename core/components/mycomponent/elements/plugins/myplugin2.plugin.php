<?php
/**
 * Mycomponent plugin2
 *
 * Copyright 2011 Your Name <you@yourdomain.com>
 *
 * @author Your Name <you@yourdomain.com>
 * @version Version 1.0.0 Beta-1
 * 1/1/11
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
 * MODx Mycomponent plugin
 *
 * Description: 
 * Events: OnBeforeManagerLogin, OnManagerLoginFormRender
 *
 * @package mycomponent
 *
 * @property
 */

/* only do this if you need lexicon strings */
$modx->lexicon->load('mycomponent:default');

/* These are examples */

switch ($modx->event->name) {
    case 'OnBeforeUserFormSave': /* register only for backend */

        /* do some stuff */
         break;

    case 'OnUserFormSave': /* register only for backend */

        /* do some other stuff */
        break;
}