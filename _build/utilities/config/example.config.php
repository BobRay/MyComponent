<?php


$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'Example',
    'packageNameLower' => 'example',
    'version' => '1.0.0',
    'release' => 'beta1',
    'category' => 'Example', /* usually the same as the package name */
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageUrl' => 'http://bobsguides.com/example-tutorial.html',
    'copyright' => '2012',
    'createdon' => strftime('%m-%d-%Y'), /* no need to edit this except to change format */
    'offerAbort' => false, /* Show package name and ask user to confirm before running */
    'packageDescription' => 'Example project for MyComponent extra.',
    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'Example',

    /* Array containing the actual Element Names used in your extra */
    'elements' => array(
        'modSnippet' => 'Snippet1,Snippet2',
        'modPlugin' => 'Plugin1,Plugin2',
        'modTemplateVar' => 'Tv1,Tv2',
        'modTemplate' => 'Template1,Template2',
        'modChunk' => 'Chunk1,Chunk2',
    ),

    /* comma-separated lists of the actual Resource pagetitles in your extra */
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

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Array of languages for which you will have language files,
     *  and comma-separated list of filenames
     *  ('.inc.php' will be added as a suffix). */
    'languages' => array(
        'en' => 'default,properties,forms',
    ),
    /* ********************************************* */
    /* Define optional directories to create under assets.
     * Add your own as needed.
     * Set to true to create directory.
     * Set to false to skip.
     * Empty js and/or css files will be created.
     */
    'hasAssets' => true,
    'minifyJS' => true,
    /* minify any JS files */
    'assetsDirs' => array(
        'css' => true,
        /* If true, a default (empty) CSS file will be created */
        'js' => true,
        /* If true, a default (empty) JS file will be created */
        'images' => true,
        'audio' => true,
        'video' => true,
        'themes' => true,
    ),


    /* Standard location for Tpl files under _build/utilities.
* If you want to alter a tpl, just duplicate it with a prefix of 'my' and
* MyComponent will use yours instead of the default */
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
        /* standard doc files */
        'docs' => 'readme.txt,license.txt,changelog.txt,tutorial.html',
        /* (optional) Description file for GitHub project home page */
        'readme.md' => true,
    ),



    'hasCore' => true, /* assume every package has a core directory */

    /* ********************************************* */
    /* (optional) Comma-separated list of extra script resolver(s) to be run
     * during install. Note that resolvers to connect plugins to events,
     * property sets to elements, resources to templates, and TVs to
     * templates will be created automatically -- *don't* list those here!
     *
     * 'default' creates a default resolver named after the package.
     * (other resolvers may be created above for TVs and plugins).
     * Suffix 'resolver.php' will be added automatically */
    'resolvers' => 'default,addUsers',

    /* (optional) Validators can abort the install after checking
     * conditions. comma-separated list of validator names (no
     * prefix of suffix) or '' 'default' creates a default resolver
     *  named after the package suffix 'validator.php' will be added */

    'validators' => 'default,hasGdLib',

    /* (optional) install.options is needed if you will interact
     * with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */
    'install.options' => 'install.options',

    /* Suffixes to use for resource and element code files */
    'suffixes' => array(
        'modPlugin' => '.php',
        'modSnippet' => '.php',
        'modChunk' => '.html',
        'modTemplate' => '.html',
        'modResource' => '.html',
    ),

    /* ********************************************* */
    /* These control the creation of elements */

    /* Create element files for the package.
    * Will not overwrite existing files */
    'createElementFiles' => true,

    /* Also create objects (snippets, chunks, etc. in MODX.
     * Will not overwrite existing objects */
    'createElementObjects' => true,

    /* (optional) Comma-separated list of elements to set as static */
    'makeStatic' => '',

    /* Create resource content files for the package.
     * Necessary if you your package has resources */
    'createResourceFiles' => true,

    /* Create empty resource objects in MODX.
     * Will not overwrite existing resources */
    'createResourceObjects' => true,

    /* (optional) will make all element objects static - makeStatic will be ignored */
    'allStatic' => false,

    /* ********************************************* */
    /* (optional) Only necessary if you will have class files.
     *
     * Array of class files to be created.
     *
     * Format is:
     *
     * 'ClassName' => 'directory:filename',
     *
     * or
     *
     *  'ClassName' => 'filename',
     *
     * ('.class.php' will be appended automatically)
     *
     *  Class file will be created as:
     * yourcomponent/core/components/yourcomponent/model/[directory/]{filename}.class.php
     *
     * Set to array() if there are no classes. */
    'classes' => array(
        'Example' => 'example:example',
    ),

    /* Comma-separated array of your new System Events (not default
     * MODX System Events). Listed here so they can be created during
     * install and removed during uninstall.
     *
     * Warning: Do *not* list regular MODX System Events here !!! */
    'newSystemEvents' => 'OnMyEvent1,OnMyEvent2',

    /* Array of plugin names and comma-separated list of their events.
     * If you need to create new system events, just list them here.
     * Automatically generates resolver to connect and/or create them.
     * (optional) Specify the priority with OnSystemEvent:## (where
     * ## is the priority you want). By default, priority will be 0 */
    'pluginEvents' => array(
        'Plugin1' => 'OnDocFormSave:1,OnUserFormSave:2,OnMyEvent1:3,OnMyEvent2:4',
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




    /* Array of template names and comma-separated
     * list of resource pagetitles.
     * Only necessary if you want to connect package resources to package
     * templates. A resolver will be created to connect them.
     * By default, all resources are given the site default template.
     * Do not include default template here!
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
    'propertySets' => 'PropertySet1,PropertySet2',

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
        'PropertySet2' => 'Chunk1:modChunk,Chunk2:modChunk',
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
    'process' => 'snippets,plugins,templateVars,templates,chunks,resources,propertySets,SystemSettings,menus',
    'pagetitles' => 'Example1,Example2', // Comma-separated list of pagetitles of resources to process.
    'parents' => '', // Comma-separated list of resource parent IDs to get children of.
    'includeParents' => false, // include listed parent resources
    /* ******************************************* */

    /* If your extra needs new System Settings, set their field values here.
     * You can also create or edit them in the Manager (System -> System Settings),
     * and export them with exportObjects. If you do that, be sure to set
     * their namespace and area to the lowercase category of your extra */

    'newSystemSettings' => array(
        'example_system_setting1' => array(  // key
            'xtype' => 'textField',
            'value' => 'value1',
            'area' => 'area1',
        ),
        'example_system_setting2' => array( // key
            'xtype' => 'combo-boolean',
            'value' => true,
            'aria' => 'area2',
        ),
    ),

    /* ******************************************* */
    /* These settings are used by LexiconHelper */
    'rewriteCodeFiles' => false, // remove ~~descriptions
    'rewriteLexiconFiles' => true, // automatically add missing strings to lexicon files
    /* ******************************************* */

    /* If your extra needs Menus, set this to true, create them
     * in the Manager, and export them with exportObjects. Be sure to set their
     * namespace to the lowercase category of your extra */
    'menus' => false,

    /* Comma-separated list of aliases used in code for the properties array.
     * Used by the checkproperties utility to check properties in code against
     * the properties in your properties transport files.
     * if you use something else, add it here (OK to remove ones you never use.
     * Search also checks with '$this->' prefix -- no need to add it here. */
    'scriptPropertiesAliases' => 'props,sp,config,scriptProperties',

);

return $components;