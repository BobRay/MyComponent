<?php
/**
 * Example class file for Example extra
 *
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 * Created on 08-17-2012
 *
 * Example is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Example is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Example; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package example
 */


$modx->lexicon->load('example:default');

include 'c:\xampp\htdocs\addons\assets\mycomponents\example\core\components\example\model\example\example3.class.php';
include 'c:\xampp\htdocs\addons\assets\mycomponents\example\core\components\example\model\example\example2.class.php';
 class Example {
    /** @var $modx modX */
    public $modx;
    /** @var $props array */
    public $props;

    function __construct(&$modx, &$config = array())
    {
        $this->modx =& $modx;
        $this->props =& $config;
        $x = $this->modx->lexicon('string1~~Hello');
        $y = $this->modx->lexicon('string2~~Goodbye');
        $z = $this->modx->lexicon('string3');

    }


}