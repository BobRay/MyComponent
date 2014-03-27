<?php

$packageNameLower = 'sitecheck'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'SiteCheck',  /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'SiteCheck project for MyComponent extra',
    'version' => '1.0.2',
    'release' => 'pl',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'http://bobsguides.com/sitecheck-tutorial.html',
    'copyright' => '2012-2013',

    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'SiteCheck',

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Set directory and file permissions for project directories */
    'dirPermission' => 0755,  /* No quotes!! */
    'filePermission' => 0644, /* No quotes!! */

    /* Define source and target directories */

    /* path to MyComponent source files */
    'mycomponentRoot' => $this->modx->getOption('mc.root', null,
        MODX_CORE_PATH . 'components/mycomponent/'),

    /* path to new project root */
    'targetRoot' => MODX_ASSETS_PATH . 'mycomponents/' . $packageNameLower . '/',


    /* *********************** NEW SYSTEM SETTINGS ************************ */

    /* If your extra needs new System Settings, set their field values here.
     * You can also create or edit them in the Manager (System -> System Settings),
     * and export them with exportObjects. If you do that, be sure to set
     * their namespace to the lowercase package name of your extra */

    'newSystemSettings' => array(
        'sitecheck.createAliases' => array( // key
            'key' => 'sitecheck.createAliases',
            'name' => 'createAliases',
            'description' => 'Create Aliases automatically for Resources with no alias; default: Yes',
            'namespace' => 'sitecheck',
            'xtype' => 'combo-boolean',
            'value' => true,
        ),
        'sitecheck.deleteInvalidPackages' => array( // key
            'key' => 'sitecheck.deleteInvalidPackages',
            'name' => 'deleteInvalidPackages',
            'description' => 'Have Sitecheck delete invalid packages that cannot be downloaded from the repository; default: No',
            'namespace' => 'sitecheck',
            'xtype' => 'combo-boolean',
            'value' => false,
        ),
        'sitecheck.maxBackups' => array( // key
            'key' => 'sitecheck.maxBackups',
            'name' => 'maxBackups',
            'description' => 'Maximum number of database backups to keep; the oldest backup will be deleted when the limit is reached; default: 5',
            'namespace' => 'sitecheck',
            'xtype' => 'textfield',
            'value' => 5,
        ),
        'sitecheck.mysqldumpCommand' => array( // key
            'key' => 'sitecheck.mysqldumpCommand',
            'name' => 'mysqldumpCommand',
            'description' => 'Command to execute the mysqldump database backup utility: default: mysqldump -u{db_user} -p{db_password} {database_name}',
            'namespace' => 'sitecheck',
            'xtype' => 'textfield',
            'value' => 'mysqldump -u{db_user} -p{db_password} {database_name}',
        ),
        'sitecheck.proceedWithoutBackup' => array( // key
            'key' => 'sitecheck.proceedWithoutBackup',
            'name' => 'proceedWithoutBackup',
            'description' => 'Perform Fix operations even if database cannot be backed up; default: No',
            'namespace' => 'sitecheck',
            'xtype' => 'combo-boolean',
            'value' => false,
        ),
    ),


    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'sitecheck' => array(
            'name' => 'sitecheck',
            'path' => '{core_path}components/sitecheck/',
            'assets_path' => '{assets_path}components/sitecheck/',
        ),

    ),

   

    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'SiteCheck' => array(
            'category' => 'SiteCheck',
            'parent' => '',  /* top level category */
        ),
        'SiteCheckProblemElements' => array(
            'category' => 'SiteCheckProblemElements',
            'parent' => 'SiteCheck',
            /* top level category */
        ),
    ),

    /* *************************** MENUS ****************************** */

    /* If your extra needs Menus, you can create them here
     * or create them in the Manager, and export them with exportObjects.
     * Be sure to set their namespace to the lowercase package name
     * of your extra.
     *
     * Every menu should have exactly one action */

    /*'menus' => array(
        'SiteCheck' => array(
            'text' => 'SiteCheck',
            'parent' => 'components',
            'description' => 'ex_menu_desc',
            'icon' => '',
            'menuindex' => 0,
            'params' => '',
            'handler' => '',
            'permissions' => '',

            'action' => array(
                'id' => '',
                'namespace' => 'sitecheck',
                'controller' => 'index',
                'haslayout' => true,
                'lang_topics' => 'sitecheck:default',
                'assets' => '',
            ),
        ),
    ),*/


    /* ************************* ELEMENTS **************************** */


    'elements' => array(

/*        'propertySets' => array( 
            'PropertySet1' => array(
                'name' => 'PropertySet1',
                'description' => 'Description for PropertySet1',
                'category' => 'SiteCheck',
            ),
            'PropertySet2' => array(
                'name' => 'PropertySet2',
                'description' => 'Description for PropertySet2',
                'category' => 'SiteCheck',
            ),
        ),*/

        'snippets' => array(
            'SiteCheck' => array(
                'category' => 'SiteCheck',
                'description' => 'Site integrity check',
                'static' => false,
            ),

        ),
        'chunks' => array(
            'SiteCheckTpl' => array(
                'category' => 'SiteCheck',
                'description' => 'Form for SiteCheck',
            ),

        ),
        /*'templates' => array(
            'Template1' => array(
                'category' => 'SiteCheck',
            ),
            'Template2' => array(
                'category' => 'SiteCheck',
                'description' => 'Description for Template two',
                'static' => false,
                'propertySets' => array(
                    'PropertySet2',
                ),
            ),
        ),*/
        /*'templateVars' => array(
            'Tv1' => array(
                'category' => 'SiteCheck',
                'description' => 'Description for TV one',
                'caption' => 'TV One',
                'propertySets' => array(
                    'PropertySet1',
                    'PropertySet2',
                ),
                'templates' => array(
                    'default' => 1,
                    'Template1' => 4,
                    'Template2' => 4,


                ),
            ),
            'Tv2' => array( 
                'category' => 'SiteCheck',
                'description' => 'Description for TV two',
                'caption' => 'TV Two',
                'static' => false,
                'default_text' => '@INHERIT',
                'templates' => array( // second value is display rank
                    'default' => 3, 
                    'Template1' => 4,
                    'Template2' => 1,
                ),
            ),
        ),*/
    ),
    /* (optional) will make all element objects static - 'static' field above will be ignored */
    'allStatic' => false,


    /* ************************* RESOURCES ****************************
     Important: This list only affects Bootstrap. There is another
     list of resources below that controls ExportObjects.
     * ************************************************************** */
    /* Array of Resource pagetitles for your Extra; All other fields optional.
       You can set any resource field here */
    'resources' => array(
        'SiteCheck Problem Resources' => array(
            'pagetitle' => 'SiteCheck Problem Resources',
            'alias' => 'sitecheck-problem-resources',
            'context_key' => 'web',
            'template' => 'default',
            'menuindex' => 0,
            'richtext' => false,
            'published' => false,
            'hidemenu' => true,
            'parent' => 0,
            'searchable' => false,
            'cacheable' => false,
        )
    ),


    /* Array of languages for which you will have language files,
     *  and comma-separated list of topics
     *  ('.inc.php' will be added as a suffix). */
    'languages' => array(
        'en' => array(
            'default',
            'properties',
            // 'properties',
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
    'minifyJS' => false,
    /* minify any JS files */
    'assetsDirs' => array(
        'css' => false,
        /* If true, a default (empty) CSS file will be created */
        'js' => false,
        /* If true, a default (empty) JS file will be created */
        'images' => false,
        'audio' => false,
        'video' => false,
        'themes' => false,
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
        // 'default',
    ),

    /* (optional) Validators can abort the install after checking
     * conditions. Array of validator names (no
     * prefix of suffix) or '' 'default' creates a default resolver
     *  named after the package suffix 'validator.php' will be added */

    'validators' => array(
        // 'default',
        // 'hasGdLib'
    ),

    /* (optional) install.options is needed if you will interact
     * with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */
    'install.options' => '',


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
        'SiteCheck' => 'sitecheck:sitecheck',
        'PathCheck' => 'sitecheck:pathcheck'
    ),

    /* *******************************************
     * These settings control exportObjects.php  *
     ******************************************* */
    /* ExportObjects will update existing files. If you set dryRun
       to '1', ExportObjects will report what it would have done
       without changing anything. Note: On some platforms,
       dryRun is *very* slow  */

    'dryRun' => '0',

    /* Array of elements to export. All elements set below will be handled.
     *
     * To export resources, be sure to list pagetitles and/or IDs of parents
     * of desired resources
    */
    'process' => array(
        'snippets',
        // 'templateVars',
        // 'templates',
        'chunks',
        'resources',
        // 'propertySets',
        // 'contextSettings',
        'menus'
    ),
    /*  Array  of resources to process. You can specify specific resources
        or parent (container) resources, or both.

        They can be specified by pagetitle or ID, but you must use the same method
        for all settings and specify it here. Important: use IDs if you have
        duplicate pagetitles */
    'getResourcesById' => false,

    'exportResources' => array(
        'SiteCheck Problem Resources'
    ),
    /* Array of resource parent IDs to get children of. */
    'parents' => array(),
    /* Also export the listed parent resources
      (set to false to include just the children) */
    'includeParents' => false,


    /* ******************** LEXICON HELPER SETTINGS ***************** */
    /* These settings are used by LexiconHelper */
    'rewriteCodeFiles' => true,
    /*# remove ~~descriptions */
    'rewriteLexiconFiles' => true,
    /* automatically add missing strings to lexicon files */
    /* ******************************************* */

     /* Array of aliases used in code for the properties array.
     * Used by the checkproperties utility to check properties in code against
     * the properties in your properties transport files.
     * if you use something else, add it here (OK to remove ones you never use.
     * Search also checks with '$this->' prefix -- no need to add it here. */
    'scriptPropertiesAliases' => array(
        'props',
        'properties',
        'sp',
        'config',
'scriptProperties'
        ),
);

return $components;
