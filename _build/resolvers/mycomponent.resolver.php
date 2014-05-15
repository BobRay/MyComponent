<?php
/**
 * Resolver for MyComponent extra
 *
 * Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
 * Created on 12-08-2012
 *
 * MyComponent is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * @package mycomponent
 * @subpackage build
 */

/* @var $object xPDOObject */
/* @var $modx modX */

/* @var array $options */


$configPath = $path = MODX_CORE_PATH . 'components/mycomponent/_build/config/';
$currentProjectFile = $configPath . 'current.project.php';
$projectsFile = $configPath . 'projects.php';

$initialCurrentProjectFile = "<" . "?" . "p" . "h" . "p" . "
/** MyComponent Current Project
 *  Change this file whenever you work on another project
 *
 *  This should be set to the lowercase name of your package and
 *  Should match the \$packageNameLower value in the Project Config
 *  file (which must be named {packageNameLower}.config.php)
 * */

\$currentProject = 'example';
";



$initialProjectsFile = "<" . "?" . "php" . "
\$projects = array(\n" .
    "    'example' => '" . MODX_ASSETS_PATH . "/mycomponents/mycomponent/_build/config/example.config.php'," .
"\n);\n\nreturn \$projects;
";



$oldStuff = array(
   'cmp.controllerheader.tpl',
   'cmp.controllerindex.tpl',
   'cmp.controllerrequest.class.php'
);
if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if (!file_exists($currentProjectFile)) {
                $fp = fopen($currentProjectFile, 'w');
                if ($fp) {
                    fwrite($fp, $initialCurrentProjectFile);
                    fclose($fp);
                } else {
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Could not write Current Project File');
                }
            }
            if (!file_exists($projectsFile)) {
                $fp = fopen($projectsFile, 'w');
                if ($fp) {
                    fwrite($fp, $initialProjectsFile);
                    fclose($fp);
                } else {
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Could not write Projects File');
                }
            }

        $path = MODX_CORE_PATH . 'components/mycomponent/_build/config/mycomponent.config.php';
            unlink($path);
            foreach($oldStuff as $name) {
                $c = $modx->getObject('modChunk', array('name' => $name));
                if ($c) {
                    $c->remove();
                }
                $path = MODX_CORE_PATH . 'components/mycomponent/elements/chunks/' . $name;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;