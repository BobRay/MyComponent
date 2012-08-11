<?php


$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'Example',
    'packageNameLower' => 'example',
    'version' => '1.0.0',
    'release' => 'beta1',
    'category' => 'Example',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageUrl' => 'http://bobsguides.com/example-tutorial.html',
    'copyright' => '2012',
    'createdon' => strftime('%m-%d-%Y'),
    'offerAbort' => false, /* Show package name and ask user to confirm before running */
    'packageDescription' => 'Example project for MyComponent extra.',
    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'Example',

    /* Change this if you need to alter any tpl files. Use a new dir. under _build/utilities.
     * Be sure to copy all build tpls to the new directory */
    'tplDir' => 'buildtpls',

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
        'css' => true, /* default (empty) CSS file will be created */
        'js' => true, /* default (empty) JS file will be created */
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
     * (other resolvers may be created above for TVs and plugins).
     * Suffix 'resolver.php' will be added automatically
     */
    'resolvers' => 'default,addUsers',

    /* validators can abort the install after checking conditions.
     * comma-separated list of validator names (no prefix of suffix) or ''
     * 'default' creates a default resolver named after the package
     * suffix 'validator.php' will be added
     */

    'validators' => 'default,hasGdLib',

    /* install.options is needed if you will interact with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name.
     */
    'install.options' => 'install.options',

    /* suffixes for resource and element code files */
    'suffixes' => array(
        'modPlugin' => '.php',
        'modSnippet' => '.php',
        'modChunk' => '.html',
        'modTemplate' => '.html',
        'modResource' => '.html',
    ),
    /* ********************************************* */
    /* These control the creation of elements */
    'createElementFiles' => true, /* create element files */
    'createElementObjects' => true, /* also create objects in MODX */
    'makeStatic' => '', /* Comma-separated list of elements to set as static */
    'createResourceFiles' => true,
    'createResourceObjects' => true,
    'allStatic' => false, /* will make all element objects static - makeStatic will be ignored */


    /* array containing the actual Element Names */
    'elements' => array(
        'modSnippet' => 'Example1,Example2',
        'modPlugin' => 'Example1,Example2',
        'modTemplateVar' => 'Example1,Example2',
        'modTemplate' => 'Example1,Example2',
        'modChunk' => 'Example1,Example2',
    ),
    /* comma-separated lists of the actual Resource pagetitles */
    'resources' => 'Example1,Example2',

    /* array of plugin names and comma-separated list of their events.
     * automatically generates resolver
     */
    'pluginEvents' => array(
        'Example1' => 'OnDocFormSave,OnUserFormSave',
        'Example2' => 'OnDocFormSave,OnUserFormSave',
    ),

    /* Array of Templates and comma-separated list of TVs to attach to them.
     * Automatically generates resolver (use 'default' for default template).
     * TV names and Template names are both case-sensitive
     */
    'templateVarTemplates' => array(
        'default' => 'Example1,Example2',
        'Example1' => 'Example1,Example2',
        'Example2' => 'Example1,Example2',
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
     * of desired resources
    */
    'process' => 'snippets,plugins,templateVars,templates,chunks',
    'pagetitles' => 'Example1,Example2', // comma-separated list of pagetitles of resources to process
    'parents' => '', // comma-separated list of parent IDs to get children of
    'includeParents' => false, // include listed parent resources

    /* ********************************************* */
    /* (NOT IMPLEMENTED) Array of new events to create, plugins to attach, and fields */
    'newEvents' => array(
        'OnEvent1' => array(
            'plugins' => 'Example',
            'fields' => array(
                'event' => 'OnEvent1',
                'priority' => 0,
                'propertyset' => 0,
            ),
        ),
        'OnEvent2' => array(
            'plugins' => 'Example',
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