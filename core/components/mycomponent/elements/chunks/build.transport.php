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
if (! class_exists('BuildHelper')) {
    class BuildHelper {

        public function __construct(&$modx) {
            /* @var $modx modX */
            $this->modx =& $modx;

        }
        public function getProps($configPath) {
            $properties = @include $configPath;
            return $properties;
        }

        public function sendLog($level, $message) {

            $msg = '';
            if ($level == MODX::LOG_LEVEL_ERROR) {
                $msg .= $this->modx->lexicon('mc_error] + $mtime[0];
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

$modx->lexicon->load('mycomponent:default');

$props = $helper->getProps(dirname(__FILE__) . '/config/' . $currentProject . '.config.php');

if (! is_array($props)) {
    die($modx->lexicon('mc_no_config_file], '-') || strpos($props['packageNameLower'], ' ') ) {
    die ($modx->lexicon("mc_space_hyphen_warning]);
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
    die ($modx->lexicon('no_categories]); /* Transfer the files in the assets dir. */
$hasCore = is_dir($sources['source_core']);   /* Transfer the files in the core dir. */

$hasContexts = file_exists($sources['data'] . 'transport.contexts.php');
$hasResources = file_exists($sources['data'] . 'transport.resources.php');
$hasValidators = is_dir($sources['build'] . 'validators'); /* Run a validators before installing anything */
$hasResolvers = is_dir($sources['build'] . 'resolvers');
$hasSetupOptions = is_dir($sources['data'] . 'install.options'); /* HTML/PHP script to interact with user */
$hasMenu = file_exists($sources['data'] . 'transport.menus.php'); /* Add items to the MODx Top Menu */
$hasSettings = file_exists($sources['data'] . 'transport.settings.php'); /* Add new MODx System Settings */
$hasContextSettings = file_exists($sources['data'] . 'transport.contextsettings.php');
$hasSubPackages = is_dir($sources['data'] .'subpackages');
$minifyJS = $modx->getOption('minifyJS', $props, false);

$helper->sendLog(MODX::LOG_LEVEL_INFO, "\n" . $modx->lexicon('mc_project? '{assets_path}components/' . PKG_NAME_LOWER . '/' : '';
$builder->registerNamespace(PKG_NAME_LOWER,false,true,'{core_path}components/'.PKG_NAME_LOWER.'/', $assetsPath);
$modx->setLogLevel(MODX::LOG_LEVEL_INFO);

/* Transport Contexts */

if ($hasContexts) {
    $contexts = include $sources['data'] . 'transport.contexts.php';
    if (!is_array($contexts)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_contexts_not_an_array] . 'transport.resources.php';
    if (!is_array($resources)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_resources_not_an_array] . 'transport.settings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_settings_not_an_array] . 'transport.contextsettings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_context_settings_not_an_array] . 'utilities/jsmin.class.php';
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
                    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_created] . $categoryName . '/transport.snippets.php');
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
    $helper->sendLog(MODX::LOG_LEVEL_INFO,
        $modx->lexicon('mc_creating_category] . $categoryName . '/transport.snippets.php';

        /* note: Snippets' default properties are set in transport.snippets.php */
        if (is_array($snippets)) {
            if ($category->addMany($snippets, 'Snippets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $categoryName.'/transport.propertysets.php';
        //  note: property set' properties are set in transport.propertysets.php
        if (is_array($propertySets)) {
            if ($category->addMany($propertySets, 'PropertySets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $categoryName .'/transport.chunks.php';
        if (is_array($chunks)) {
            if ($category->addMany($chunks, 'Chunks')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $categoryName .'/transport.templates.php';
        if (is_array($templates)) {
            if ($category->addMany($templates, 'Templates')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $categoryName .'/transport.tvs.php';
        if (is_array($tvs)) {
            if ($category->addMany($tvs, 'TemplateVars')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $categoryName . '/transport.plugins.php';
        if (is_array($plugins)) {
            if ($category->addMany($plugins, 'Plugins')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged&& $i == 1) { /* only install these on first pass */
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
        $helper->sendLog(MODX::LOG_LEVEL_INFO,
            $modx->lexicon('mc_processing_validators]) ? array() : $props['validators'];
        if (! empty($validators)) {
            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $validator = PKG_NAME_LOWER;
                }
                $file = $sources['validators'] . $validator . '.validator.php';
                if (file_exists($file)) {
                    $helper->sendLog(modX::LOG_LEVEL_INFO,'    ' . $modx->lexicon('mc_packaging&& $hasResolvers) { /* add resolvers to last category only */
        $resolvers = empty($props['resolvers'])? array() : $props['resolvers'];
        $resolvers = array_merge(array('category','plugin','tv','resource','propertyset'), $resolvers);
        $helper->sendLog(MODX::LOG_LEVEL_INFO,
            $modx->lexicon('mc_processing_resolvers] . $resolver . '.resolver.php';
            if (file_exists($file)) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_packaged] . $resolver . '.resolver.php',
                ));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $modx->lexicon('mc_no&& $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO,
            $modx->lexicon('mc_packaged_core_files],
                'target' => "return MODX_CORE_PATH . 'components/';",
            ));
    }

    /* This section transfers every file in the local
       mycomponents/mycomponent/assets directory to the
       target site's assets/mycomponent directory on install.
     */

    if ($hasAssets && $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO,
            $modx->lexicon('mc_packaged_assets_files],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ));
    }

    /* Add subpackages */
    /* The transport.zip files will be copied to core/packages
     * but will have to be installed manually with "Add New Package and
     *  "Search Locally for Packages" in Package Manager
     */

    if ($hasSubPackages && $i == 1) {
        $helper->sendLog(modX::LOG_LEVEL_INFO,
            $modx->lexicon('mc_packaging_subpackages],
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
    $helper->sendLog(modX::LOG_LEVEL_INFO,
        $modx->lexicon('mc_packaging_menu].'transport.menus.php';
    foreach ($menus as $menu) {
        if (empty($menu)) {
            $helper->sendLog(modX::LOG_LEVEL_ERROR,
                $modx->lexicon('mc_could_not_package_menu] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
);

if ($hasSetupOptions && !empty($props['install.options'])) {
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

$helper->sendLog(xPDO::LOG_LEVEL_INFO, $modx->lexicon('mc_package_built