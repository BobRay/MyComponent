<?php

$packageNameLower = 'unittest'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'UnitTest',
    'packageDescription' => 'UnitTest project for MyComponent extra',
    'packageNameLower' => $packageNameLower,
    'version' => '1.0.0',
    'release' => 'beta1',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'http://bobsguides.com/unit-test-tutorial.html',
    'copyright' => '2012',
    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'Example',

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Set directory and file permissions for project directories */
    'dirPermission' => 0755,
    /* No quotes!! */
    'filePermission' => 0644,
    /* No quotes!! */

    /* Change this if you need to alter any tpl files. Use a new dir. under _build/utilities.
     * Be sure to copy all build tpls to the new directory */
    'tplDir' => 'buildtpls',

    /* Define source and target directories (mycomponent root and core directories) */
    'mycomponentRoot' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/',
    /* path to MyComponent source files */
    'mycomponentCore' => MODX_ASSETS_PATH . 'mycomponents/mycomponent/core/components/mycomponent/',
    /* path to new project root */
    'targetRoot' => MODX_ASSETS_PATH . 'mycomponents/' . $packageNameLower . '/',

    /* If your extra needs new System Settings, set their field values here.
  * You can also create or edit them in the Manager (System -> System Settings),
  * and export them with exportObjects. If you do that, be sure to set
  * their namespace and area to the lowercase category of your extra */

    'newSystemSettings' => array(
        'ut_system_setting1' => array(
            'key' => 'ut_system_setting1',
            'xtype' => 'textfield',
            'value' => 'value1',
            'namespace' => 'unittest',
        ),
        'ut_system_setting2' => array(
            'key' => 'ut_system_setting2',
            'xtype' => 'combo-boolean',
            'value' => true,
        ),
    ),

    /* Comma-separated array of your new System Events (not default
        * MODX System Events). Listed here so they can be created during
        * install and removed during uninstall.
        *
        * Warning: Do *not* list regular MODX System Events here !!! */
    'newSystemEvents' => array(
        'OnUtEvent1' => array(
            'name' => 'OnUtEvent1',
        ),
        'OnUtEvent2' => array(
            'name' => 'OnUtEvent2',
            'groupname' => 'Example',
            'service' => 1,
        ),
    ),

    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'example' => array(
            'name' => 'unittest',
            'path' => '{core_path}components/unittest/',
            'assets_path' => '{assets_path}components/unittest/',
        ),

    ),

    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageNameLower
     * or want to nest categories.
    */

    'categories' => array(
        'UnitTest' => array(
            'category' => 'UnitTest',
            'parent' => '',
            /* top level category */
        ),
        'utCategory2' => array(
            'category' => 'utCategory2',
            'parent' => 'UnitTest',
            /* nested under Example */
        )
    ),

    /* *************************** MENUS ****************************** */

    /* If your extra needs Menus, set this to true, create them
     * in the Manager, and export them with exportObjects. Be sure
     * to set their namespace to the lowercase package name of your extra */
    'menus' => false,

    /* ************************* ELEMENTS **************************** */

    /* Array containing elements for your extra. 'category' is required
       for each element, all other fields are optional.
       Property Sets (if any) must come first! */


    'elements' => array(

        'propertySets' => array( /* all three fields are required */
            'utPropertySet1' => array(
                'name' => 'utPropertySet1',
                'description' => 'Description for utPropertySet1',
                'category' => 'UnitTest',
            ),
            'utPropertySet2' => array(
                'name' => 'utPropertySet2',
                'description' => 'Description for utPropertySet2',
                'category' => 'UnitTest',
            ),
        ),

        'snippets' => array(
            'utSnippet1' => array( /* minimal example */
                'category' => 'UnitTest',
                'static' => true,
            ),

            'utSnippet2' => array( /* example with static and property set(s)  */
                'category' => 'UtCategory2',
                'static' => false,
                'propertySets' => array(
                    'utPropertySet1',
                    'utPropertySet2'
                ),
            ),

        ),
        'plugins' => array(
            'utPlugin1' => array( /* minimal example */
                'category' => 'UnitTest',
            ),
            'utPlugin2' => array( /* example with static, events, and property sets */
                'category' => 'UnitTest',
                'static' => false,
                'propertySets' => array( /* all property sets to be connected to element */
                    'utPropertySet1',
                ),
                'events' => array(
                    /* minimal example - no fields */
                    'OnUserFormSave' => array(),
                    /* example with fields set */
                    'OnUtEvent1' => array(
                        'priority' => '0',
                        /* priority of the event -- 0 is highest priority */
                        'group' => 'plugins',
                        /* should generally be set to 'plugins' */
                        'propertySet' => 'utPropertySet1',
                        /* property set to be used in this pluginEvent */
                    ),
                    'OnUtEvent2' => array(
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
            'utChunk1' => array(
                'category' => 'UnitTest',
            ),
            'utChunk2' => array(
                'category' => 'UnitTest',
                'static' => false,
                'propertySets' => array(
                    'utPropertySet2',
                ),
            ),
        ),
        'templates' => array(
            'utTemplate1' => array(
                'category' => 'UnitTest',
            ),
            'utTemplate2' => array(
                'category' => 'UnitTest',
                'static' => false,
                'propertySets' => array(
                    'utPropertySet2',
                ),
            ),
        ),
        'templateVars' => array( /* minimal example */
            'utTv1' => array(
                'category' => 'UnitTest',
                'propertySets' => array(
                    'utPropertySet1',
                    'utPropertySet2',
                ),
            ),
            'utTv2' => array( /* example with templates, default, and static specified */
                'category' => 'UnitTest',
                'static' => false,
                'default_text' => '@INHERIT',
                'templates' => array(
                    'default' => 3,
                    /* second value is rank -- for ordering TVs when editing resource */
                    'utTemplate1' => 4,
                    'utTemplate2' => 1,
                ),
            ),
        ),
    ),
    /* (optional) will make all element objects static - 'static' field above will be ignored */
    'allStatic' => false,


    /* ************************* RESOURCES **************************** */

    /* Array of Resource pagetitles for your Extra; All other fields optional.
       You can set any resource field here */
    'resources' => array(
        'utResource1' => array( /* minimal example */
            'pagetitle' => 'utResource1',
            'alias' => 'ut-resource1',
        ),
        'utResource2' => array( /* example with other fields */
            'pagetitle' => 'utResource2',
            'parent' => 'utResource1',
            'template' => 'utTemplate1',
            'richtext' => false,
            'published' => true,
            'tvValues' => array(
                'utTv1' => 'SomeValue',
                'utTv2' => 'SomeOtherValue',
            ),
        )
    ),


    /* Array of languages for which you will have language files and
     * comma-separated list of filenames ('.inc.php' will be added as a suffix) */
    'languages' => array(
        'en' =>  array(
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

    /* Suffixes for resource and element code files */
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
      * These settings control exportObjects.php and bootstrap.php  *
      ******************************************* */
    /* ExportObjects will update existing files. You may want to set
       dryRun to '1' in the early stages of a new project so it
       will report what it would have done withougt changing anything  */

    /* This only affects ExportObjects */
    'dryRun' => '0',

    /* These settings control both Bootstrap and Export Objects */
    'createTransportFiles' => true,  // remove??
    'createObjectFiles' => true,    // remove??

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
        'utResource1',
        'utResource2'
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