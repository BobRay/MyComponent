<?php

$packageNameLower = 'foryourapproval'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'ForYourApproval',
    /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'Hold user web site changes for approval',
    'version' => '1.0.0',
    'release' => 'beta1',
    'author' => 'Bob Ray',
    'email' => '<https://bobsguides.com>',
    'authorUrl' => 'https://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'https://bobsguides.com/foryourapproval-tutorial.html',
    'copyright' => '2012-2013',

    /* no need to edit this except to change format */
    'createdon' => date("m-d-Y"),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'ForYourApproval',

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Set directory and file permissions for project directories */
    'dirPermission' => 0755,
    /* No quotes!! */
    'filePermission' => 0644,
    /* No quotes!! */

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
        'fya_drafts_id' => array(
            'key' => 'fya_drafts_id',
            'name' => 'FYA Drafts ID',
            'description' => 'ID of ForYourApproval Draft container Resource',
            'namespace' => 'foryourapproval',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'ForYourApproval',
        ),
        'fya_archive_id' => array(
            'key' => 'fya_archive_id',
            'name' => 'ForYourApproval Archive ID',
            'description' => 'ID of ForYourApproval Archive container Resource',
            'namespace' => 'foryourapproval',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'ForYourApproval',
        ),
        'fya_groups' => array(
            'key' => 'fya_groups',
            'name' => 'ForYourApproval Groups',
            'description' => 'Comma-separated list of User Groups whose work will be subject to approval',
            'namespace' => 'foryourapproval',
            'xtype' => 'textfield',
            'value' => 'FyaContributors',
            'area' => 'ForYourApproval',
        ),
        'fya_archive_original' => array(
            'key' => 'fya_archive_original',
            'name' => 'ForYourApproval Archive Original',
            'description' => 'If set, previous versions of updated Resources will be archived',
            'namespace' => 'foryourapproval',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'ForYourApproval',
        ),
        'fya_include_tvs' => array(
            'key' => 'fya_include_tvs',
            'name' => 'ForYourApproval Include TVs',
            'description' => 'If set, TV values of the resource will be updated',
            'namespace' => 'foryourapproval',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'ForYourApproval',
        ),
        'fya_update_publishedon_date' => array(
            'key' => 'fya_update_publishedon_date',
            'name' => 'ForYourApproval Update Published On Date',
            'description' => 'If set, the Published On date of the original resource will be updated to the Stage Date',
            'namespace' => 'foryourapproval',
            'xtype' => 'combo-boolean',
            'value' => false,
            'area' => 'ForYourApproval',
        ),
    ),

    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'foryourapproval' => array(
            'name' => 'foryourapproval',
            'path' => '{core_path}components/foryourapproval/',
            'assets_path' => '{assets_path}components/foryourapproval/',
        ),

    ),


    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'ForYourApproval' => array(
            'category' => 'ForYourApproval',
            'parent' => '',
            /* top level category */
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

            'ForYourApproval' => array( /* foryourapproval with static, events, and property sets */
                'category' => 'ForYourApproval',
                'description' => 'Hold Resource changes until approved',
                'static' => false,

                'events' => array(
                    /* minimal foryourapproval - no fields */

                    /* foryourapproval with fields set */
                    'OnDocFormSave' => array(
                        'priority' => '0',
                        /* priority of the event -- 0 is highest priority */
                        'group' => 'plugins',
                        /* should generally be set to 'plugins' */
                    ),
                    'OnLoadWebDocument' => array(
                        'priority' => '0',
                        'group' => 'plugins',
                    ),

                ),
            ),
        ),
        'templateVars' => array(
            'Approved' => array(
                'category' => 'ForYourApproval',
                'description' => 'Changes are approved for publication',
                'caption' => 'Approved',
                'type' => 'checkbox',
                'default_text' => 'No',

                'templates' => array(
                    'default' => 1,
                ),
            ),

            'DraftId' => array(
                'category' => 'ForYourApproval',
                'description' => 'ID of draft Resource (set automatically)',
                'caption' => 'Draft ID',
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
        'FYA Drafts' => array(
            'pagetitle' => 'FYA Staged Resources',
            'longtitle' => 'FYA Staged Resources',
            'description' => 'Container for FYA Drafts',
            'alias' => 'fya-staged-resources',
            'richtext' => false,
            'published' => false,
            'hidemenu' => true,
            'content' => '<p>Container for FYA Drafts</p>',
        ),
        'FYA Archive' => array(
            'pagetitle' => 'FYA Archive',
            'longtitle' => 'FYA Archive',
            'alias' => 'fya-archive',
            'richtext' => false,
            'published' => false,
            'hidemenu' => true,
            'content' => '<p>Container for FYA Archived Resources</p>',
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
    'assetsDirs' => array(),


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
        'templaces',
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
        'FYA Drafts',
        'FYA Archive',
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
