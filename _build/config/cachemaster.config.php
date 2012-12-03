<?php


$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'CacheMaster',
    'packageNameLower' => 'cachemaster',
    'version' => '1.0.0',
    'release' => 'beta1',
    'category' => 'CacheMaster',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageUrl' => 'http://bobsguides.com/cachemaster-tutorial.html',
    'copyright' => '2012',
    'createdon' => strftime('%m-%d-%Y'),
    'packageDescription' => 'CacheMaster allows you to clear the MODX cache for a single resource when saving it.',

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'CacheMaster',

    /* Set directory permissions for project directories */
    'dirPermission' => 0755, /* No quotes!! */
    'filePermission' => 0644, /* No quotes!! */

    /* Define source directories (mycomponent root and core directories) */
    'source' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/', /* path to MyComponent source files */
    'sourceCore' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/',

    /* ********************************************* */
    /* Define default directories and files to be created in project*/
    'initialize' => true,
    'defaultStuff' => array(
        'utilities' => false, /* copy entire utilities dir to target dir -- Usually unnecessary */
        'lexicon' => true, /* create lexicon directory */
        'docs' => 'readme.txt,license.txt,changelog.txt,tutorial.html',
        'readme.md' => true, /* Description file for GitHub project home page */
        'languages' => 'en', /* only list languages for which you have language files */
    ),

    'hasCore' => true, /* assume every package has a core directory */

    /* ********************************************* */
    /* Define optional directories to create under assets
     * add your own as needed
     * set to true to create directory
     * set to false to skip
     * Empty js and css files will be created
     */
    'hasAssets' => true,
    'minifyJS' => true, /* minify any JS files */
    'assetsDirs' => array(
        'css' => true,
        'js' => true,
        'images' => true,
        'audio' => true,
        'video' => true,
        'themes' => true,
    ),
    /* ********************************************* */
    /* comma-separated list of extra script resolver(s) to be run
     * during install. Note that resolvers to connect plugins to events
     * and TVs to templates will be created automatically -- don't list those here
     *
     * 'default' creates a default resolver named after the package
     * (other resolvers may be created above for TVs and plugins)
     * suffix 'resolver.php' will be added
     */
    'resolvers' => 'default,extra',

    /* validators can abort the install after checking conditions.
     * comma-separated list of validator names (no prefix of suffix) or ''
     * 'default' creates a default resolver named after the package
     * suffix 'validator.php' will be added
     */

    'validators' => 'default,extra',

    /* install.options is needed if you will interact with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name.
     */
    'install.options' => 'install.options',

    /* suffixes for files */
    'suffixes' => array(
        'plugin' => '.php',
        'snippet' => '.php',
        'chunk' => '.html',
        'template' => '.html',
        'tv' => '.tv',
        'resource' => '.html',
        'default' => '.php',
    ),
    /* ********************************************* */
    /* These control the creation of elements */
    'createElementFiles' => true, /* create element files */
    'createElementObjects' => true, /* also create objects in MODX */
    'makeStatic' => 'CacheMaster', /* Comma-separated list of elements to set as static */
    'createResourceObjects' => false,
    'createResourceFiles' => false,
    'allStatic' => true, /* will make all element objects static - makeStatic will be ignored */


    /* array containing the actual Element Names */
    'elements' => array(
        'snippets' => '',
        'plugins' => 'CacheMaster',
        'tvs' => '',
        'templates' => '',
        'chunks' => '',
    ),
    /* comma-separated lists of the actual Resource pagetitles */
    'resources' => '',

    /* array of plugin names and comma-separated list of their events.
     * automatically generates resolver
     */
    'pluginEvents' => array(
        'CacheMaster' => 'OnDocFormSave',
    ),

    /* Array of Templates and comma-separated list of TVs to attach to them.
     * Automatically generates resolver (use 'default' for default template).
     * TV names and Template names are both case-sensitive
     */
    'templateVarTemplates' => array(
        'default' => 'MyTvOne,MyTvTwo',
        'Collapsible' => 'MyTvOne,MyTvTwo,MyTvThree',
    ),
    /* ********************************************* */
    /* These properties control exportObjects.php */
    'dryRun' => '0',
    'createTransportFiles' => '1',
    'createObjectFiles' => '1',
    /* comma-separated list of elements to export. All elements in the category
     * set above will be handled.
     *
     * To export resources, list pagetitles and/or IDs of parents
     * of desired resources (
    */
    'process' => 'plugins,templateVars',
    'pagetitles' => 'Notify,NotifyPreview', // comma-separated list of pagetitles of resources to process
    'parents' => '', // comma-separated list of parent IDs to get children of
    'includeParents' => false,  // include listed parent resources

    /* ********************************************* */
    /* (NOT IMPLEMENTED) Array of new events to create, plugins to attach, and fields */
    'newEvents' => array(
        'OnEvent1' => array(
            'plugins' => 'CacheMaster',
            'fields' => array(
                'event' => 'OnEvent1',
                'priority' => 0,
                'propertyset' => 0,
            ),
        ),
        'OnEvent2' => array(
            'plugins' => 'CacheMaster',
            'fields' => array(
                'event' => 'OnEvent2',
                'priority' => 0,
                'propertyset' => 0,
            ),
        )
    ),
    /* (NOT IMPLEMENTED) comma-separated lists naming other new objects
     * to create */
    /* ToDo: Implement Property Sets */
    'otherObjects' => array(
        'menus' => '',
        'propertySets' => '',
        'newSystemEvents' => '', /* *new* System Events to be created for the extra */
        'newSystemSettings' => '', /* *new* SystemSettings to be created for the extra */

    )
);

return $components;