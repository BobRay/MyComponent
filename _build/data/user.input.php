<?php

/**
 * Script to interact with user during MyComponent package install
 *
 * Copyright 2011 Your Name <you@yourdomain.com>
 * @author Your Name <you@yourdomain.com>
 * 1/15/11
 *
 *  is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 *  is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */
/**
 * Description: Script to interact with user during MyComponent package install
 * @package mycomponent
 * @subpackage build
 */
/* Use these if you would like to do different things depending on what's happening */
switch ($options[XPDO_TRANSPORT_PACKAGE_ACTION]) {
    case XPDO_TRANSPORT_ACTION_INSTALL: break;
    case XPDO_TRANSPORT_ACTION_UPGRADE: break;
    case XPDO_TRANSPORT_ACTION_UNINSTALL: break;
}

/* The return value from this script should be an HTML form (minus the
 * <form> tags and submit button) in a single string.
 *
 * The form will be shown to the user during install
 * after the readme.txt display.
 *
 * This example presents an HTML form to the user with one input field
 * (you can have more).
 *
 * The user's entries in the form's input field(s) will be available
 * in any php resolvers as $option['field_name'].
 * In this example, it's $option['user_email'].
 *
 * You can use the value(s) to set system settings, snippet properties,
 * chunk content, etc. One common use is to use a checkbox and ask the
 * user if they would like to install an example resource for your
 * component.
 */

$output = '<p>&nbsp;</p>
<label for="user_email">Please enter the email address you would like MyComponent to send to.</label>

<p>&nbsp;</p>
<input type="text" name="user_email" id="user_email" value="" align="left" width="60" maxlength="60" />
<p>&nbsp;</p>
<p>
Note: You can change this easily, or add multiple recipients, later<br />
by going to the MyComponent snippet page, selecting the "properties" tab,<br />
and changing the value of the recipientArray property.</p>';

return $output;