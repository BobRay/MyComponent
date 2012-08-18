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
    'scriptPropertiesAlias' => 'props',

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
    /* Define optional directories to create under assets.
     * Add your own as needed.
     * Set to true to create directory.
     * Set to false to skip.
     * Empty js and/or css files will be created.
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
    /* Comma-separated list of extra script resolver(s) to be run
     * during install. Note that resolvers to connect plugins to events,
     * property sets to elements, resources to templates, and TVs to
     * templates will be created automatically -- *don't* list those here!
     *
     * 'default' creates a default resolver named after the package.
     * (other resolvers may be created above for TVs and plugins).
     * Suffix 'resolver.php' will be added automatically */
    'resolvers' => 'default,addUsers',

    /* Validators can abort the install after checking conditions.
     * comma-separated list of validator names (no prefix of suffix) or ''
     * 'default' creates a default resolver named after the package
     * suffix 'validator.php' will be added */

    'validators' => 'default,hasGdLib',

    /* install.options is needed if you will interact with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */
    'install.options' => 'install.options',

    /* Suffixes for resource and element code files */
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

    /* ********************************************* */
    /* Array of class files to be created.
     * Format is:
     * 'ClassName' => 'directory:filename',  ('.class.php' will be appended automatically)
     *  Class file will be created as:
     * yourcomponent/core/components/yourcomponent/model/{directory}/{filename}.class.php
     *
     * Set to array() if there are no classes. */
    'classes' => array(
        'Example' => 'example:example',
    ),

    /* Array containing the actual Element Names */
    'elements' => array(
        'modSnippet' => 'Snippet1,Snippet2',
        'modPlugin' => 'Plugin1,Plugin2',
        'modTemplateVar' => 'Tv1,Tv2',
        'modTemplate' => 'Template1,Template2',
        'modChunk' => 'Chunk1,Chunk2',
    ),

    /* Comma-separated array of new System Events (not default
     * MODX System Events). Listed here so they can be removed
     * during uninstall. Warning: Do *not* list regular
     * System Events here !!! */
    'newSystemEvents' => 'OnMyEvent1,OnMyEvent2',

    /* Array of plugin names and comma-separated list of their events.
     * If you need to create new system events, just list them here.
     * Automatically generates resolver to connect and/or create them. */
    'pluginEvents' => array(
        'Plugin1' => 'OnDocFormSave,OnUserFormSave,OnMyEvent1,OnMyEvent2',
        'Plugin2' => 'OnDocFormSave,OnUserFormSave,OnMyEvent1,OnMyEvent2',
    ),
    /* Array of Templates and comma-separated list of TVs to attach to them.
     * Automatically generates resolver to connect them
     * (use 'default' for the site's default template).
     * TV names and Template names are both case-sensitive */
    'templateVarTemplates' => array(
        'default' => 'Tv1,Tv2',
        'Template1' => 'Tv1,Tv2',
        'Template2' => 'Tv1,Tv2',
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

    /* (NOT IMPLEMENTED) Array of template names and comma-separated
     * list of resource pagetitles.
     * Only necessary if you want to connect package resources to package
     * templates. A resolver will be created to connect them.
     * By default, all resources are given the site default template.
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
     *     */
    'TvResourceValues' => array(
        'Tv1' => array(
            'Resource1' => 'someValue',
            'Resource2' => 'someOtherValue',
        ),
        'Tv2' => array(
            'Resource1' => 'someOtherValue',
        ),
    ),

    /* Comma-separated list of property set names to create.
     * Property set has no properties. Created here so it can
     * be connected to elements in a resolver. Create the properties
     * in the Manager and export them with exportObjects */
    'propertySets' => 'PropertySet1',

    /* Array of property sets and elements to connect them to.
     * form is:
     * 'propertySetElements' => array(
     *    'propertySetName1' => 'elementName:elementType,elementName:elementType, ...',
     *    'propertySetName2' => 'elementName:elementType,elementName:elementType, ...',
     * );
     * Type must be specified because you might have different elements
     * with the same name.
     *
     * A resolver to connect them will be created automatically
    */
    'propertySetElements' => array(
        'PropertySet1' => 'Plugin1:modPlugin,Snippet1:modSnippet',
    ),

    /* *******************************************
     * These settings control exportObjects.php  *
     ******************************************* */
    'dryRun' => '0',
    'createTransportFiles' => '1',
    'createObjectFiles' => '1',
    /* Comma-separated list of elements to export. All elements in the category
     * set above will be handled.
     *
     * To export resources, list pagetitles and/or IDs of parents
     * of desired resources
    */
    'process' => 'snippets,plugins,templateVars,templates,chunks,resources,propertySets',
    'pagetitles' => 'Example1,Example2', // Comma-separated list of pagetitles of resources to process.
    'parents' => '', // Comma-separated list of resource parent IDs to get children of.
    'includeParents' => false, // include listed parent resources
    /* ******************************************* */

    /* If your extra needs new System Settings, set this to true, create them
     * in the Manager and export them with exportObjects. Be sure to set their
     * namespace to the lowercase category or packageName of your extra */

    'NewSystemSettings' => false,


    /* If your extra needs Menus, set this to true, create them
     * in the Manager and export them with exportObjects. Be sure to set their
     * namespace to the lowercase category or packageName of your extra */
    'menus' => false,

);

return $components;