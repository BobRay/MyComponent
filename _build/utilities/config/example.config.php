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
        'docs' => 'readme.txt,license.txt,changelog.txt,tutorial.html',
        'readme.md' => true, /* Description file for GitHub project home page */
    ),
    /* Array of languages for which you will have language files and
     * comma-separated list of filenames ('.inc.php' will be added as a suffix) */
    'languages' => array(
        'en' => 'default,properties',
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
        'modSnippet' => 'Snippet1,Snippet2',
        'modPlugin' => 'Plugin1,Plugin2',
        'modTemplateVar' => 'Tv1,Tv2',
        'modTemplate' => 'Template1,Template2',
        'modChunk' => 'Chunk1,Chunk2',
    ),
    /* array of plugin names and comma-separated list of their events.
     * automatically generates resolver to connect them
     */
    'pluginEvents' => array(
        'Plugin1' => 'OnDocFormSave,OnUserFormSave',
        'Plugin2' => 'OnDocFormSave,OnUserFormSave',
    ),
    /* Array of Templates and comma-separated list of TVs to attach to them.
     * Automatically generates resolver to connect them
     * (use 'default' for default template).
     * TV names and Template names are both case-sensitive
     */
    'templateVarTemplates' => array(
        'default' => 'Tv1,Tv2',
        'Template1' => 'Tv1,Tv2',
        'Example2' => 'Tv1,Tv2',
    ),

    /* comma-separated lists of the actual Resource pagetitles */
    /* ToDo: Make sure resources are created near the top of the build script */
    'resources' => 'Resource1,Resource2',

    /* set these only if you want to override system defaults */
    'resource_defaults' => array(
        //'published' => false,
        //'richtext' => false,
        //'hidemenu' => true,
        //'cacheable' => false,
        //'searchable' => true,
        //'context' => 'web',
        //'template' => 12,  /* must be a template ID */
    ),
    /* (NOT IMPLEMENTED) array of template names and comma-separated
     * list of resource pagetitles.
     * Only necessary if you want to connect package resources to package
     * templates. A resolver will be created to connect them.
     * By default, all resources are given the site default template
     */

    'resourceTemplates' => array(
        'Template1' => 'Resource1,Resource2',
    ),
    /* ToDo: make sure this resolver runs last */
    /* (NOT IMPLEMENTED) TV Resource Values - set TV values for specific resources.
     * A resolver will be created automatically
     * Format is:
     *    'TvName' => array(
     *       'pagetitle' => 'value'
     *    ),
    */
    'TvResourceValues' => array(
        'Tv1' => array(
            'Resource1' => 'someValue',
        ),
        'Tv2' => array(
            'Resource1' => 'someOtherValue',
        ),
    ),

    /* Comma-separated list of property set names to create */
    'propertySets' => 'PropertySet1',

    /* (NOT IMPLEMENTED) Array of property sets and elements to connect them to.
     * A resolver to connect them will be created automatically
    */
    'propertySetElements' => array(
        'PropertySet1' => array(
                'Plugin1' => 'modPlugin',
                'Snippet1' => 'modSnippet',
        ),
    ),

    /* *******************************************
     * These settings control exportObjects.php  *
     ******************************************* */
    'dryRun' => '0',
    'createTransportFiles' => '1',
    'createObjectFiles' => '1',
    /* comma-separated list of elements to export. All elements in the category
     * set above will be handled.
     *
     * To export resources, list pagetitles and/or IDs of parents
     * of desired resources
    */
    'process' => 'snippets,plugins,templateVars,templates,chunks,resources,propertySets',
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

        'newSystemEvents' => '', /* *new* System Events to be created for the extra */
        'newSystemSettings' => '', /* *new* SystemSettings to be created for the extra */

    )
);

return $components;