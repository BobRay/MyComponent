<?php
/* use this as an example. Copy to componentname.config.php in this directory */

$components = array (
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'CacheMaster',
    'packageNameLower' => 'cachemaster',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'copyright' => '2012',
    'createdon' => strftime('%m-%d-%Y'),
    'offerAbort' => false, /* Show package name and ask user to confirm before running */

/* Set directory permissions for project directories */
    'dirPermission' => 0755,  /* No quotes!! */
    'filePermission' => 0644,  /* No quotes!! */

    /* Define source directories (mycomponent root and core directories) */
    'source' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/', /* path to MyComponent source files */
    'sourceCore' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/',

    /* Define default directories and files to be created in project*/
    'initialize' => true,
    'defaultStuff' => array(
        '_build' => true, /* build directory for transport package */
        'lexicon' => true, /* location for lexicon files */
        'docs' => true, /* readme.txt, license, changelog, and/or tutorial(s) */
        'readme.md' => true, /* Description file for GitHub project home page */
        'languages' => 'en,fr', /* only list languages for which you have language files */
    ),
    /* Define optional directories -- comment out ones you don't need */
    'optionalDirs' => array(
        'css',
        'js',
        'images',
        'audio',
        'video',
        /* extra script resolver(s) to be run during install */
        'resolvers',
        /* validators can abort the install after checking conditions */
        'validators',
        /* needed if you will interact with user during the install */
        'install.options',
    ),
    /* suffixes for files */
    'suffixes' => array (
        'plugin' => '.php',
        'snippet' => '.php',
        'chunk' => '.html',
        'template' => '.html',
        'tv' => '.tv',
        'resource' => '.html',
        'default' => '.php',
    ),

    /* These control the creation of elements */
    'createFiles' => true,  /* create element files */
    'createObjects' => true, /* also create objects in MODX */
    'makeStatic' => 'CacheMaster', /* Comma-separated list of objects to set as static */
    'allStatic' => true, /* will make all objects static - makeStatic will be ignored */

    /* comma-separated lists of the actual Element Names */
    'elements' => array(
        'snippets' => '',
        'plugins' => 'CacheMaster',
        'tvs' => '',
        'templates' => '',
        'chunks' => '',
        ),
    /* comma-separated lists of the actual Resource Names */
    'resources' => array(

    ),
    /* (NOT IMPLEMENTED) comma-separated lists naming other new objects
     * to create */
    'otherObjects' => array(
        'menus' => '',
        'systemEvents' => '',
        'propertySets' => '',
        'systemSettings' => '',

    )
);

return $components;