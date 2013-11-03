<?php

$packageNameLower = 'stagecoach'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'StageCoach',  /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'StageCoach project for MyComponent extra',
    'version' => '1.2.0',
    'release' => 'pl',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'http://bobsguides.com/stagecoach-tutorial.html',
    'copyright' => '2012-2013',

    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'StageCoach',

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
        'stagecoach_resource_id' => array(
            'key' => 'stagecoach_resource_id',
            'name' => 'StageCoach Resource ID',
            'description' => 'ID of StageCoach container Resource',
            'namespace' => 'stagecoach',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'StageCoach',
        ),
        'stagecoach_archive_id' => array(
            'key' => 'stagecoach_archive_id',
            'name' => 'StageCoach Archive ID',
            'description' => 'ID of StageCoach Archive container Resource',
            'namespace' => 'stagecoach',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'StageCoach',
        ),
        'stagecoach_archive_original' => array(
            'key' => 'stagecoach_archive_original',
            'name' => 'StageCoach Archive Original',
            'description' => 'If set, previous versions of updated Resources will be archived',
            'namespace' => 'stagecoach',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'StageCoach',
        ),
        'stagecoach_include_tvs' => array(
            'key' => 'stagecoach_include_tvs',
            'name' => 'StageCoach Include TVs',
            'description' => 'If set, TV values of the resource will be updated',
            'namespace' => 'stagecoach',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'StageCoach',
        ),
        'stagecoach_update_publishedon_date' => array(
            'key' => 'stagecoach_update_publishedon_date',
            'name' => 'StageCoach Update Published On Date',
            'description' => 'If set, the Published On date of the original resource will be updated to the Stage Date',
            'namespace' => 'stagecoach',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'StageCoach',
        ),
        'stagecoach_stage_date_tv_id' => array(
            'key' => 'stagecoach_stage_date_tv_id',
            'name' => 'StageCoach Stage Date TV ID',
            'description' => 'ID of the StageDate TV',
            'namespace' => 'stagecoach',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'StageCoach',
        ),
        'stagecoach_staged_resource_tv_id' => array(
            'key' => 'stagecoach_staged_resource_tv_id',
            'name' => 'StageCoach Staged Resource ID',
            'description' => 'ID of the Staged Resource TV',
            'namespace' => 'stagecoach',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'StageCoach',
        ),
    ),

       /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'stagecoach' => array(
            'name' => 'stagecoach',
            'path' => '{core_path}components/stagecoach/',
            'assets_path' => '{assets_path}components/stagecoach/',
        ),

    ),


    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'StageCoach' => array(
            'category' => 'StageCoach',
            'parent' => '',  /* top level category */
        ),
    ),


    /* ************************* ELEMENTS **************************** */

    /* Array containing elements for your extra. 'category' is required
       for each element, all other fields are optional.
       Property Sets (if any) must come first!

       The standard file names are in this form:
           SnippetName.snippet.php
           PluginName.plugin.php
           ChunkName.chunk.html
           TemplateName.template.html

       If your file names are not standard, add this field:
          'filename' => 'actualFileName',
    */


    'elements' => array(

        'plugins' => array(

            'StageCoach' => array( /* stagecoach with static, events, and property sets */
                'category' => 'StageCoach',
                'description' => 'Stages Resources for future update',
                'static' => false,

                'events' => array(
                    /* minimal stagecoach - no fields */

                    /* stagecoach with fields set */
                    'OnDocFormSave' => array(
                        'priority' => '0', /* priority of the event -- 0 is highest priority */
                        'group' => 'plugins', /* should generally be set to 'plugins' */
                    ),
                    'OnWebPageInit' => array(
                        'priority' => '0',
                        'group' => 'plugins',
                    ),

                ),
            ),
        ),
        'templateVars' => array(
            'StageDate' => array(
                'category' => 'StageCoach',
                'description' => 'Date Resource will be updated',
                'caption' => 'Stage Date',
                'type' => 'date',
                'default_text' => '',

                'templates' => array(
                    'default' => 1,
                ),
            ),
            'StageID' => array(
                'category' => 'StageCoach',
                'description' => 'ID of staged Resource (set automatically)',
                'caption' => 'Stage ID',
                'type' => 'textfield',
                'default_text' => '',

                'templates' => array(
                    'default' => 1,
                ),
            ),
        ),
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
        'Staged Resources' => array(
            'pagetitle' => 'Staged Resources',
            'alias' => 'staged-resources',
            'richtext' => false,
            'published' => false,
            'hidemenu' => true,
        ),
        'StageCoach Archive' => array(
            'pagetitle' => 'StageCoach Archive',
            'alias' => 'stagecoach-archive',
            'richtext' => false,
            'published' => false,
            ),

    ),


    /* Array of languages for which you will have language files,
     *  and comma-separated list of topics
     *  ('.inc.php' will be added as a suffix). */
    'languages' => array(
        'en' => array(
            'default',
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
    ),

    /* (optional) Validators can abort the install after checking
     * conditions. Array of validator names (no
     * prefix of suffix) or '' 'default' creates a default resolver
     *  named after the package suffix 'validator.php' will be added */


    /* (optional) install.options is needed if you will interact
     * with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */



    /* Suffixes to use for resource and element code files (not implemented)  */
    'suffixes' => array(
        'modPlugin' => '.php',
        'modSnippet' => '.php',
        'modChunk' => '.html',
        'modTemplate' => '.html',
        'modResource' => '.html',
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
        'plugins',
        'templateVars',
        'templates',
        'resources',
        'systemSettings',
    ),
    /*  Array  of resources to process. You can specify specific resources
        or parent (container) resources, or both.

        They can be specified by pagetitle or ID, but you must use the same method
        for all settings and specify it here. Important: use IDs if you have
        duplicate pagetitles */
    'getResourcesById' => false,

    'exportResources' => array(
        'Staged Resources',
        'StageCoach Archive',
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
        'sp',
        'config',
'scriptProperties'
        ),
);

return $components;
