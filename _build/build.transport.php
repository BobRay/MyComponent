<?php
/**
 * Important: You should almost never need to edit this file,
 * except to add components that it won't handle (e.g., permissions,
 * users, policies, policy templates, ACL entries, and Form
 * Customization rules), and most of those might better be handled
 * in a script resolver, which you can add without editing this file.
 *
 * Important note: MyComponent never updates this file, so any changes
 * you make will be permanent.
 *
 * Build Script for MyComponent extra
 *
 * Copyright 2012 by Bob Ray <http://bobsguides.com>
 * Created on 10-23-2012
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
 *
 * @package mycomponent
 * @subpackage build
 */

/**
 * This is the template for the build script, which creates the
 * transport.zip file for your extra.
 *

 */
/* See the tutorial at http://http://bobsguides.com/mycomponent-tutorial.html
 * for more detailed information about using the package.
 */

/* config file must be retrieved in a class */
class BuildHelper {

    public function __construct(&$modx) {
        $this->modx =& $modx;

    }
    public function getProps($configPath) {
        $properties = @include $configPath;
        return $properties;
    }

    public function sendLog($level, $message) {
        $msg = '';
        if ($level == MODX::LOG_LEVEL_ERROR) {
            $msg .= 'ERROR -- ';
        }
        $msg .= $message;
        $msg .= "\n";
        if (php_sapi_name() != 'cli') {
            $msg = nl2br($msg);
        }
        echo $msg;
    }
}

/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);


/* Instantiate MODx -- if this require fails, check your
 * _build/build.config.php file
 */
require_once dirname(dirname(__FILE__)) . '/_build/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE
    ? 'ECHO'
    : 'HTML');

if (!defined('MODX_CORE_PATH')) {
    die('build.config.php is not correct');
 }

@include dirname(__FILE__) . '/config/current.project.php';

if (! $currentProject) {
    die('Could not get current project');
}

$helper = new BuildHelper($modx);
$props = $helper->getProps(dirname(__FILE__) . '/config/' . $currentProject . '.config.php');

if (! is_array($props)) {
    die('Could not get project config file');
}

if (strpos($props['packageNameLower'], '-') || strpos($props['packageNameLower'], ' ') ) {
    die ("\$packageNameLower cannot contain spaces or hyphens");
}
/* Set package info. These are initially set from the the the project config
 * but feel free to hard-code them for future versions */

define('PKG_NAME', $props['packageName']);
define('PKG_NAME_LOWER', $props['packageNameLower']);
define('PKG_VERSION', $props['version']);
define('PKG_RELEASE', $props['release']);


/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources= array (
    'root' => $root,
    'build' => $root . '_build/',
    'config' => $root . '_build/config/',
    'utilities' => $root . '_build/utilities/',
    /* note that the next two must not have a trailing slash */
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
    'resolvers' => $root . '_build/resolvers/',
    'validators' => $root . '_build/validators/',
    'data' => $root . '_build/data/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'install_options' => $root . '_build/install.options/',
    'packages' => $root . 'core/packages',  /* no trailing slash */

);
unset($root);




$categories = require_once $sources['build'] . 'config/categories.php';

if (empty ($categories)) {
    die ("No Categories");
}

/* Set package options - you can set these manually, but it's
 * recommended to let them be generated automatically
 */

$hasAssets = is_dir($sources['source_assets']); /* Transfer the files in the assets dir. */
$hasCore = is_dir($sources['source_core']);   /* Transfer the files in the core dir. */
$hasResources = file_exists($sources['data'] . 'transport.resources.php');
$hasValidators = is_dir($sources['build'] . 'validators'); /* Run a validators before installing anything */
$hasResolvers = is_dir($sources['data'] . 'resolvers');
$hasSetupOptions = is_dir($sources['data'] . 'install.options'); /* HTML/PHP script to interact with user */
$hasMenu = file_exists($sources['data'] . 'transport.menus.php'); /* Add items to the MODx Top Menu */
$hasSettings = file_exists($sources['data'] . 'transport.settings.php'); /* Add new MODx System Settings */
$hasSubPackages = is_dir($sources['data'] .'subpackages');
$minifyJS = $props['minifyJS'];


/* load builder */
$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$assetsPath = $hasAssets? '{assets_path}components/' . PKG_NAME_LOWER . '/' : '';
$builder->registerNamespace(PKG_NAME_LOWER,false,true,'{core_path}components/'.PKG_NAME_LOWER.'/', $assetsPath);

/* Transport Resources */

if ($hasResources) {
    $resources = include $sources['data'] . 'transport.resources.php';
    if (!is_array($resources)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, 'Resources not an array.');
    } else {
        $attributes = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'pagetitle',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'ContentType' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                ),
            ),
        );
        foreach ($resources as $resource) {
            $vehicle = $builder->createVehicle($resource, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Packaged ' . count($resources) . ' resources.');
    }
    unset($resources, $resource, $attributes);
}

/* load new system settings */
if ($hasSettings) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, 'Settings not an array.');
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
        );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Packaged ' . count($settings) . ' new System Settings.');
        unset($settings, $setting, $attributes);
    }
}

/* minify JS */

if ($minifyJS) {
    $helper->sendLog(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');
    // require $sources['build'] . 'utilities/jsmin.class.php';
    require $sources['utilities'] . 'jsmin.class.php';

    $jsDir = $sources['source_assets'] . '/js';

    if (is_dir($jsDir)) {
        $files = scandir($jsDir);
        foreach ($files as $file) {
            /* skip non-js and already minified files */
            if ( (!stristr($file, '.js') || strstr($file,'min'))) {
                continue;
            }

            $jsmin = JSMin::minify(file_get_contents($sources['source_assets'] . '/js/' . $file));
            if (!empty($jsmin)) {
                $outFile = $jsDir . '/' . str_ireplace('.js', '-min.js', $file);
                $fp = fopen($outFile, 'w');
                if ($fp) {
                    fwrite($fp, $jsmin);
                    fclose($fp);
                    $helper->sendLog(modX::LOG_LEVEL_INFO, 'Created: ' . $outFile);
                } else {
                    $helper->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open min.js outfile: ' . $outFile);
                }
            }
        }

    } else {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, 'Could not open JS directory.');
    }
}

/* Create each Category and its Elements */

$i = 0;
$count = count($categories);

foreach($categories as $k => $categoryName) {
    /* @var $categoryName string */

    /* See what we have based on the files */
    $hasSnippets = file_exists($sources['data'] . $categoryName . '/transport.snippets.php');
    $hasChunks = file_exists($sources['data'] . $categoryName . '/transport.chunks.php');
    $hasTemplates = file_exists($sources['data'] . $categoryName . '/transport.templates.php');
    $hasTemplateVariables = file_exists($sources['data'] . $categoryName . '/transport.tvs.php');
    $hasPlugins = file_exists($sources['data'] . $categoryName . '/transport.plugins.php');
    $hasPropertySets = file_exists($sources['data'] . $categoryName . '/transport.propertysets.php');

    /* @var $category modCategory */
    $category= $modx->newObject('modCategory');
    $i++;  /* will be 1 for the first category */
    $category->set('id',$i);
    $category->set('category',$categoryName);
    $helper->sendLog(MODX::LOG_LEVEL_INFO, "Creating Category: " . $categoryName);
    $helper->sendLog(MODX::LOG_LEVEL_INFO, "Processing Elements in Category: " . $categoryName);

    /* add snippets */
    if ($hasSnippets) {

        $snippets = include $sources['data'] . $categoryName . '/transport.snippets.php';

        /* note: Snippets' default properties are set in transport.snippets.php */
        if (is_array($snippets)) {
            if ($category->addMany($snippets, 'Snippets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($snippets) . ' Snippets.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL,'    Adding Snippets failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.snippets.php');
        }
    }

    if ($hasPropertySets) {
        $propertySets = include $sources['data'] . $categoryName.'/transport.propertysets.php';
        //  note: property set' properties are set in transport.propertysets.php
        if (is_array($propertySets)) {
            if ($category->addMany($propertySets, 'PropertySets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($propertySets) . ' Property Sets.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Adding Property Sets failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.propertysets.php');
        }
    }
    if ($hasChunks) { /* add chunks  */
    $helper->sendLog(modX::LOG_LEVEL_INFO,'Adding Chunks.');
        /* note: Chunks' default properties are set in transport.chunks.php */
        $chunks = include $sources['data'] . $categoryName .'/transport.chunks.php';
        if (is_array($chunks)) {
            if ($category->addMany($chunks, 'Chunks')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($chunks) . ' Chunks.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Adding Chunks failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.chunks.php');
        }
    }


    if ($hasTemplates) { /* add templates  */
    $helper->sendLog(modX::LOG_LEVEL_INFO,'Adding Templates.');
        /* note: Templates' default properties are set in transport.templates.php */
        $templates = include $sources['data'] . $categoryName .'/transport.templates.php';
        if (is_array($templates)) {
            if ($category->addMany($templates, 'Templates')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($templates) . ' Templates.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Adding Templates failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.templates.php');
        }
    }

    if ($hasTemplateVariables) { /* add template variables  */
    $helper->sendLog(modX::LOG_LEVEL_INFO,'Adding Template Variables.');
    /* note: Template Variables' default properties are set in transport.tvs.php */
        $tvs = include $sources['data'] . $categoryName .'/transport.tvs.php';
        if (is_array($tvs)) {
            if ($category->addMany($tvs, 'TemplateVars')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($tvs) . ' TVs.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Adding TVs failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.tvs.php');
        }
    }


    if ($hasPlugins) {
        /* Plugins' default properties are set in transport.plugins.php */
        $plugins = include $sources['data'] . $categoryName . '/transport.plugins.php';
        if (is_array($plugins)) {
            if ($category->addMany($plugins, 'Plugins')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . count($plugins) . ' Plugins.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Adding Plugins failed.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    Non-array in transport.plugins.php');
        }
    }

    /* Create Category attributes array dynamically
     * based on which elements are present
     */

    $attr = array(xPDOTransport::UNIQUE_KEY => 'category',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
    );

    if ($hasValidators && $i == 1) { /* only install these on first pass */
          $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
    }

    if ($hasSnippets) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasPropertySets) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['PropertySets'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasChunks) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasPlugins) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        );
    }

    if ($hasTemplates) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'templatename',
        );
    }

    if ($hasTemplateVariables) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['TemplateVars'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        );
    }

    /* create a vehicle for the category and all the things
     * we've added to it.
     */
    $vehicle = $builder->createVehicle($category,$attr);

    if ($hasValidators && $i == 1) { /* only install these on first pass */
        $helper->sendLog(MODX::LOG_LEVEL_INFO, 'Processing Validators');
        $validators = empty($props['validators']) ? array() : $props['validators'];
        if (! empty($validators)) {
            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $validator = PKG_NAME_LOWER;
                }
                $file = $sources['validators'] . $validator . '.validator.php';
                if (file_exists($file)) {
                    $helper->sendLog(modX::LOG_LEVEL_INFO,'    Packaging ' . $validator . ' Validator.');
                    $vehicle->validate('php',array(
                        'source' => $file,
                    ));
                } else {
                    $helper->sendLog(modX::LOG_LEVEL_ERROR, 'Could not find Validator file: ' . $file );
                }
            }
        }
    }

    /* Package script resolvers, if any */
    if ($i == $count) { /* add resolvers to last category only */
        $resolvers = empty($props['resolvers'])? array() : $props['resolvers'];
        $resolvers = array_merge(array('category','plugin','tv','resource','propertyset'), $resolvers);
        $helper->sendLog(MODX::LOG_LEVEL_INFO, 'Processing Resolvers');

        foreach ($resolvers as $resolver) {
            if ($resolver == 'default') {
                $resolver = PKG_NAME_LOWER;
            }

            $file = $sources['resolvers'] . $resolver . '.resolver.php';
            if (file_exists($file)) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    Packaged ' . $resolver . ' resolver.');
                $vehicle->resolve('php', array(
                    'source' => $sources['resolvers'] . $resolver . '.resolver.php',
                ));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    No ' . $resolver . ' resolver.');
            }
        }
    }
    /* This section transfers every file in the local
       mycomponents/mycomponent/core directory to the
       target site's core/mycomponent directory on install.
     */

    if ($hasCore && $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO, 'Packaged core directory files');
        $vehicle->resolve('file',array(
                'source' => $sources['source_core'],
                'target' => "return MODX_CORE_PATH . 'components/';",
            ));
    }

    /* This is just for MyComponent - Transfer _build dir. */
    $helper->sendLog(MODX::LOG_LEVEL_INFO, 'Packaged _build directory files');
    $vehicle->resolve('file',array(
            'source' => $sources['root'] . '/_build/config',
            'target' => "return MODX_CORE_PATH . 'components/mycomponent/_build/';",
        ));

    /* This section transfers every file in the local
       mycomponents/mycomponent/assets directory to the
       target site's assets/mycomponent directory on install.
     */

    if ($hasAssets && $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO, 'Packaged assets directory files');
        $vehicle->resolve('file',array(
            'source' => $sources['source_assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ));
    }

    /* Add subpackages */
    /* The transport.zip files will be copied to core/packages
     * but will have to be installed manually with "Add New Package and
     *  "Search Locally for Packages" in Package Manager
     */

    if ($hasSubPackages && $i == 1) {
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Packaging subpackages.');
         $vehicle->resolve('file',array(
            'source' => $sources['packages'],
            'target' => "return MODX_CORE_PATH;",
            ));
    }

    /* Put the category vehicle (with all the stuff we added to the
     * category) into the package
     */
    $builder->putVehicle($vehicle);
}
/* Transport Menus */
if ($hasMenu) {
    /* load menu */
    $helper->sendLog(modX::LOG_LEVEL_INFO,'Packaging menu...');
    $menus = include $sources['data'].'transport.menus.php';
    foreach ($menus as $menu) {
        if (empty($menu)) {
            $helper->sendLog(modX::LOG_LEVEL_ERROR,'Could not package menu.');
        } else {
            $vehicle= $builder->createVehicle($menu,array (
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'text',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
                'Action' => array (
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
                ),
            ),
    ));
            $builder->putVehicle($vehicle);
            unset($vehicle, $menu);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Packaged ' . count($menus) . ' menu items.');
    }
}

/* Next-to-last step - pack in the license file, readme.txt, changelog,
 * and setup options 
 */
$attr = array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
);

if (!empty($props['install.options'])) {
    $attr['setup-options'] = array(
        'source' => $sources['install_options'] . 'user.input.php',
    );
} else {
    $attr['setup-options'] = array();
}
$builder->setPackageAttributes($attr);

/* Last step - zip up the package */
$builder->pack();

/* report how long it took */
$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$helper->sendLog(xPDO::LOG_LEVEL_INFO, "Package Built.");
$helper->sendLog(xPDO::LOG_LEVEL_INFO, "Execution time: {$totalTime}");
exit();
