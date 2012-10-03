<?php

$packageNameLower = 'example'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'Example',
    /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'Example extra for MODX Revolution - a tool for building MODX Transport Packages',
    'version' => '1.0.0',
    'release' => 'beta1',
    'categories' => array( /* usually just one and the same as the package name */
        'Example,'
    ),
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'http://bobsguides.com/example-tutorial.html',
    'copyright' => '2012',
    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),
    /* Show package name and ask user to confirm before running */
    'offerAbort' => false,
    /* require login -- only necessary if on a live server */
    'requireLogin' => false,

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'Mycomponent',
    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',
    /* Set directory and file permissions for project directories */
    'dirPermission' => 0755,
    /* No quotes!! */
    'filePermission' => 0644,
    /* No quotes!! */

    /* Define source and target directories (mycomponent root and core directories) */
    'mycomponentRoot' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/',
    /* path to MyComponent source files */
    'mycomponentCore' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/core/components/mycomponent/',
    /* path to new project root */
    'targetRoot' => MODX_ASSETS_PATH . 'mycomponents/' . $packageNameLower . '/',


    /* *********************** NEW SYSTEM SETTINGS ************************ */

    /* If your extra needs new System Settings, set their field values here.
     * You can also create or edit them in the Manager (System -> System Settings),
     * and export them with exportObjects. If you do that, be sure to set
     * their namespace and area to the lowercase package name of your extra */

    /*'newSystemSettings' => array(
        'example_system_setting1' => array( // key
            'namespace' => 'example',
            'xtype' => 'textField',
            'value' => 'value1',
            'area' => 'area1',
        ),
        'example_system_setting2' => array( // key
            'namespace' => 'example',
            'xtype' => 'combo-boolean',
            'value' => true,
            'area' => 'area2',
        ),
    ),*/

    /* ************************ NEW SYSTEM EVENTS ************************* */

    /* Array of your new System Events (not default
     * MODX System Events). Listed here so they can be created during
     * install and removed during uninstall.
     *
     * Warning: Do *not* list regular MODX System Events here !!! */

    /*   'newSystemEvents' => array(
        'OnMyEvent1',
        'OnMyEvent2',
    ),*/

    /* ************************* PROPERTY SETS **************************** */

    /* Array of property set names to create.
     * List just the names of property sets. They will be creted with no
     * properties and will be connected to any elements yous specify below.
     * Create the properties in the Manager and export them with exportObjects */

    /*   'propertySets' => array(
        'PropertySet1',
        'PropertySet2'
    ),*/

    /* ************************* ELEMENTS **************************** */

    /* If your extra needs Menus, set this to true, create them
     * in the Manager, and export them with exportObjects. Be sure
     * to set their namespace to the lowercase package name of your extra */

    //'menus' => true,

    /* ************************* ELEMENTS **************************** */

    /* Array containing elements for your extra. 'category' is required
all other fields are optional */


    'elements' => array(
        'snippets' => array(
            'Snippet1' => array( /* minimal example */
                'category' => 'Example',
            ),

            'Snippet2' => array( /* example with static and property set(s)  */
                'category' => 'Example',
                'static' => false,
                'propertySets' => array(
                    'PropertySet1',
                    'PropertySet2'
                ),
            ),

        ),
        'plugins' => array(
            'Plugin1' => array( /* minimal example */
                'category' => 'Example',
            ),
            'Plugin2' => array( /* example with static, events, and property sets */
                'category' => 'Example',
                'static' => false,
                'propertySets' => array(
                    'PropertySet1',
                ),
                'events' => array(
                    'OnUserFormSave' => array(),
                    /* minimal example - no fields */
                    'OnMyEvent1' => array( /* example with fields set */
                        'priority' => '0',
                        /* priority of the event -- 0 is highest priority */
                        'group' => 'plugins',
                        /* should generally be set to 'plugins' */
                        'propertySet' => 'PropertySet1',
                    ),
                    'OnMyEvent2' => array(
                        'priority' => '',
                        'group' => 'plugins',
                        'propertySet' => '',
                    ),
                    'OnDocFormSave' => array(
                        'priority' => '0',
                        'group' => 'plugins',
                        'propertySet' => '',
                    ),


                ),
            ),
        ),
        'chunks' => array(
            'Chunk1' => array(
                'category' => 'Example',
            ),
            'Chunk2' => array(
                'category' => 'Example',
                'static' => false,
                'propertySet' => 'PropertySet2',
            ),
        ),
        'templates' => array(
            'Template1' => array(
                'category' => 'Example',
            ),
            'Template2' => array(
                'category' => 'Example',
                'static' => false,
                'propertySet' => 'PropertySet2',
            ),
        ),
        'tvs' => array( /* minimal example */
            'Tv1' => array(
                'category' => 'Example',
            ),
            'Tv2' => array( /* example with templates, default, and static specified */
                'category' => 'Example',
                'static' => false,
                'default_text' => '@INHERIT',
                'templates' => array(
                    'default',
                    'Template1',
                    'Template2',
                ),
            ),
        ),
    ),
    /* (optional) will make all element objects static - 'static' field above will be ignored */
    'allStatic' => false,

    /* Element Connections */

    /* Array of plugin names and events (optionally) each one's prority.
     * By default, priority will be 0.
     * Automatically generates resolver to connect and/or create them. */
    /*   'pluginEvents' => array(
        'Plugin1' => array(
            'OnDocFormSave' => 1,
            'OnUserFormSave' => 2,
            'OnMyEvent1' => 3,
            'OnMyEvent2' => 4,
            ),
        'Plugin2' => array(
            'OnDocFormSave' => '',
            'OnUserFormSave' => '',
            'OnMyEvent1' => '',
            'OnMyEvent2' => '',
        ),
    ),*/
    /* Array of Templates and comma-separated list of TVs to attach to them.
     * Automatically generates resolver to connect them
     * (use 'default' for the site's default template).
     * TV names and Template names are both case-sensitive */
    /*   'templateVarTemplates' => array(
        'default' => array('Tv1',
            'Tv2'
        ),
        'Template1' => array(
            'Tv1',
            'Tv2'),
        'Template2' => array(
            'Tv1',
            'Tv2'
        ),
    ),*/

    /* Array of property sets and elements to connect them to.
     * form is:
     * 'propertySetElements' => array(
     *    'propertySetName1' => = array(
     *        'elementName' => 'elementType'
     *        'elementName' => 'elementType,
     *           ...',
     *     ),
     *    'propertySetName2' => = array(
     *        'elementName' => 'elementType'
     *        'elementName' => 'elementType,
     *           ...',
     *     ),
     * ),

     * Type must be specified because you might have different elements
     * with the same name.
     *
     * A resolver to connect them will be created automatically
    */
    /*   'propertySetElements' => array(
        'PropertySet1' => array(
            'Plugin1' => 'modPlugin',
            'Snippet1' => 'modSnippet',
        ),
        'PropertySet2' => array(
            'Chunk1' => 'modChunk',
            'Chunk2' => 'modChunk',
        ),
    ),*/

    /* ************************* RESOURCES **************************** */

    /* Array of Resource pagetitles for your Extra; All other fields optional.
       You can set any resource field here */
    'resources' => array(
        'Resource1' => array( /* minimal example */
            'pagetitle' => 'Resource1',
        ),
        'Resource2' => array( /* example with other fields */
            'pagetitle' => 'Resource2',
            'alias' => 'resource2',
            'parent' => 'Resource1',
            'template' => 'Template1',
            'richtext' => false,
            'published' => true,
            'tvValues' => array(
                'Tv1' => 'SomeValue',
                'Tv2' => 'SomeOtherValue',
            ),
        )
    ),


    /* Array of template names and the pagetitles of resources that use them.
    * Only necessary if you want to connect package resources to package
    * templates. A resolver will be created to connect them.
    * By default, all resources are given the site default template.
    * Do not include default template here!
    */

    /*   'resourceTemplates' => array(
        'Template1' => array(
            'Resource1',
            'Resource2'
        ),
    ),*/

    /* ToDo: make sure this resolver runs last */
    /* (NOT IMPLEMENTED) TV Resource Values - set TV values for specific resources.
     * A resolver will be created automatically
     * Format is:
     *    'TvName' => array(
     *       'pagetitle' => 'value'
     *    ),
     *     */
    /*    'TvResourceValues' => array(
        'Tv1' => array(
            'Resource1' => 'someValue',
            'Resource2' => 'someOtherValue',
        ),
        'Tv2' => array(
            'Resource1' => 'someOtherValue',
        ),
    ),*/


    /* Resource Parents - array of 'resource pagetitle' => 'resource parent pagetitle'
       Resources no listed here will be placed in the web root */
    /*    'resourceParents' => array(
        'Resource1' => '',
        'Resource2' => 'Resource1',
    ),*/


    /* Array of languages for which you will have language files,
*  and comma-separated list of topics
*  ('.inc.php' will be added as a suffix). */
    'languages' => array(
        'en' => array(
            'default',
            'properties',
            'forms',
        ),
    ),
    /* ********************************************* */
    /* Define optional directories to create under assets.
     * Add your own as needed.
     * Set to true to create directory.
     * Set to hasAssets = false to skip.
     * Empty js and/or css files will be created.
     */
    'hasAssets' => false,
    'minifyJS' => false,
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


    /* ********************************************* */
    /* Define basic directories and files to be created in project*/

    'docs' => array(
        'readme.txt',
        'license.txt',
        'changelog.txt',
        'tutorial.html'
    ),

    /* (optional) Description file for GitHub project home page */
    'readme.md' => true,
    /* assume every package has a core directory */
    'hasCore' => true,

    /* ********************************************* */
    /* (optional) Array of extra script resolver(s) to be run
     * during install. Note that resolvers to connect plugins to events,
     * property sets to elements, resources to templates, and TVs to
     * templates will be created automatically -- *don't* list those here!
     *
     * 'default' creates a default resolver named after the package.
     * (other resolvers may be created above for TVs and plugins).
     * Suffix 'resolver.php' will be added automatically */
    'resolvers' => array(
        'default',
        'addUsers'
    ),

    /* (optional) Validators can abort the install after checking
     * conditions. Array of validator names (no
     * prefix of suffix) or '' 'default' creates a default resolver
     *  named after the package suffix 'validator.php' will be added */

    'validators' => array(
        'default',
        'hasGdLib'
    ),

    /* (optional) install.options is needed if you will interact
     * with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */
    'install.options' => 'install.options',


    /* Suffixes to use for resource and element code files (not implemented)  */
    'suffixes' => array(
        'modPlugin' => '.php',
        'modSnippet' => '.php',
        'modChunk' => '.html',
        'modTemplate' => '.html',
        'modResource' => '.html',
    ),


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

    /* *******************************************
     * These settings control exportObjects.php  *
     ******************************************* */
    /* ExportObjects will update existing files. You may want to set
       dryRun to '1' in the early stages of a new project so it
       will report what it would have done withougt changing anything  */

    'dryRun' => '0',
    'createTransportFiles' => '1',
    // remove??
    'createObjectFiles' => '1',
    // remove??

    /* Array of elements to export. All elements set above will be handled.
     *
     * To export resources, be sure to list pagetitles and/or IDs of parents
     * of desired resources
    */
    'process' => array(
        'snippets',
        'plugins',
        'templateVars',
        'templates',
        'chunks',
        'resources',
        'propertySets',
        'SystemSettings',
        'menus'
    ),
    /*  Array  of pagetitles of resources to process. */
    'pagetitles' => array(
        'Example1',
        'Example2'
    ),
    /* Array of resource parent IDs to get children of. */
    'parents' => array(),
    /* ToDo: Add is_numeric check */
    /* Also export the listed parent resources
      (set to false to include just the children) */
    'includeParents' => false,


    /* ******************** LEXICON HELPPER SETTINGS ***************** */
    /* These settings are used by LexiconHelper */
    'rewriteCodeFiles' => false,
    // remove ~~descriptions
    'rewriteLexiconFiles' => true,
    // automatically add missing strings to lexicon files
    /* ******************************************* */

    /* Array of aliases used in code for the properties array.
     * Used by the checkproperties utility to check properties in code against
     * the properties in your properties transport files.
     * if you use something else, add it here (OK to remove ones you never use.
     * Search also checks with '$this->' prefix -- no need to add it here. */
    'scriptPropertiesAliases' => array(
        'props',
        'sp',
        'config',
        'scriptProperties'
    ),
);

return $components;