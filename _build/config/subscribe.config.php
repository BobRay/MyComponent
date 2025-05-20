<?php

$packageNameLower = 'subscribe'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'Subscribe',  /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'Subscribe manages user registration',
    'version' => '1.3.0',
    'release' => 'pl',
    'author' => 'Bob Ray',
    'email' => '<https://bobsguides.com>',
    'authorUrl' => 'https://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'https://bobsguides.com/subscribe-tutorial.html',
    'copyright' => '2012-2022',

    /* no need to edit this except to change format */
    'createdon' => date("m-d-Y"),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'Subscribe',

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
        'sbs_register_page_id' => array(
            'key' => 'sbs_register_page_id',
            'namespace' => 'subscribe',
            'xtype' => 'textfield',
            'value' => '999',
            'area' => 'subscribe',
        ),
        'sbs_login_page_id' => array(
            'key' => 'sbs_login_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_confirm_register_page_id' => array(
            'key' => 'sbs_confirm_register_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_manage_prefs_page_id' => array(
            'key' => 'sbs_manage_prefs_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_registration_confirmed_page_id' => array(
            'key' => 'sbs_registration_confirmed_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_thank_you_page_id' => array(
            'key' => 'sbs_thank_you_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'prefListTpl' => array(
            'key' => 'prefListTpl',
            'value' => 'sbsPrefListTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'groupListTpl' => array(
            'key' => 'groupListTpl',
            'value' => 'sbsGroupListTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'checkboxTpl' => array(
            'key' => 'checkboxTpl',
            'value' => 'sbsCheckboxTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'loggedOutDisplayTpl' => array(
            'key' => 'loggedOutDisplayTpl',
            'value' => 'sbsLoggedOutDisplayTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'loggedInDisplayTpl' => array(
            'key' => 'loggedInDisplayTpl',
            'value' => 'sbsLoggedInDisplayTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'whyDialogTpl' => array(
            'key' => 'whyDialogTpl',
            'value' => 'sbsWhyDialogTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'whyDialogTextTpl' => array(
            'key' => 'whyDialogTextTpl',
            'value' => 'sbsWhyDialogTextTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'privacyDialogTpl' => array(
            'key' => 'privacyDialogTpl',
            'value' => 'sbsPrivacyDialogTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'privacyDialogTextTpl' => array(
            'key' => 'privacyDialogTextTpl',
            'value' => 'sbsPrivacyDialogTextTpl',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbsCssPath' => array(
            'key' => 'sbsCssPath',
            'value' => '{assets_url}components/subscribe/css/',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbsCssFile' => array(
            'key' => 'sbsCssFile',
            'value' => 'subscribe.css',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbsJsPath' => array(
            'key' => 'sbsJsPath',
            'value' => '{assets_url}components/subscribe/js/',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbsJsFile' => array(
            'key' => 'sbsJsFile',
            'value' => 'subscribe.js',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_use_comment_field' => array(
            'key' => 'sbs_use_comment_field',
            'value' => '1',
            'xtype' => 'combo-boolean',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_extended_field' => array(
            'key' => 'sbs_extended_field',
            'value' => 'interests',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'language' => array(
            'key' => 'language',
            'value' => 'en',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_secret_key' => array(
            'key' => 'sbs_secret_key',
            'value' => '',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_unsubscribe_page_id' => array(
            'key' => 'sbs_unsubscribe_page_id',
            'value' => '999',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_user_roles' => array(
            'key' => 'sbs_user_roles',
            'value' => '',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_show_interests' => array(
            'key' => 'sbs_show_interests',
            'value' => true,
            'xtype' => 'combo-boolean',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_show_groups' => array(
            'key' => 'sbs_show_groups',
            'value' => false,
            'xtype' => 'combo-boolean',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_field_name' => array(
            'key' => 'sbs_field_name',
            'value' => 'interests',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
        'sbs_groups_field_name' => array(
            'key' => 'sbs_groups_field_name',
            'value' => 'groups',
            'xtype' => 'textfield',
            'namespace' => 'subscribe',
            'area' => 'subscribe',
        ),
    ),

    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'subscribe' => array(
            'name' => 'subscribe',
            'path' => '{core_path}components/subscribe/',
            'assets_path' => '{assets_path}components/subscribe/',
        ),

    ),

    /* ************************ CONTEXT(S) ************************* */
    /* (optional) List any contexts other than the 'web' context here
    */

    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'Subscribe' => array(
            'category' => 'Subscribe',
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

        'snippets' => array(
            'sbsInterestReport' => array(
                'category' => 'Subscribe',
                'description' => 'Report on subscribers',
                'static' => false,
            ),
            'sbsSubscribeForm' => array(
                'category' => 'Subscribe',
                'description' => 'Creates the subscribe form',
                'static' => false,
            ),

            'sbsSubscribeRequest' => array(
                'category' => 'Subscribe',
                'description' => 'Creates the Request to Subscribe display',
                'static' => false,
            ),
            'sbsUnsubscribe' => array(
                'category' => 'Subscribe',
                'description' => 'Processes incoming links from Notify or EmailResource unsubscribe links',
                'static' => false,
            ),

        ),

        'chunks' => array(
            'sbsActivationEmailTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsCheckBoxTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsGroupListTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsLoggedInDisplayTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsLoggedOutDisplayTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsManagePrefsFormTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsPrefListTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsPrivacyDialogTextTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsPrivacyDialogTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsRegisterFormTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsUserNotFoundTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsWhyDialogTextTpl' => array(
                'category' => 'Subscribe',
            ),
            'sbsWhyDialogTpl' => array(
                'category' => 'Subscribe',
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
        'Subscribe Folder' => array( /* minimal subscribe */
            'pagetitle' => 'Subscribe Folder',
            'alias' => 'subscribe-folder',
        ),
        'Subscribe' => array( /* subscribe with other fields */
            'pagetitle' => 'Subscribe',
            'alias' => 'subscribe',
        ),
        'Manage Preferences' => array( /* subscribe with other fields */
            'pagetitle' => 'Manage Preferences',
            'alias' => 'manage-preferences',
        ),

        'Thank You for Registering' => array( /* subscribe with other fields */
            'pagetitle' => 'Thank You for Registering',
            'alias' => 'thanks-for-registering',
        ),

        'Registration Confirmed' => array( /* subscribe with other fields */
            'pagetitle' => 'Registration Confirmed',
            'alias' => 'registration-confirmed',
        ),

        'ConfirmRegister' => array( /* subscribe with other fields */
            'pagetitle' => 'ConfirmRegister',
            'alias' => 'confirm-register',
        ),

        'Unsubscribe' => array( /* subscribe with other fields */
            'pagetitle' => 'Unsubscribe',
            'alias' => 'unsubscribe',
        ),

    ),


    /* Array of languages for which you will have language files,
     *  and comma-separated list of topics
     *  ('.inc.php' will be added as a suffix). */
    'languages' => array(
        'en' => array(
            'default',
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

    /* Dependencies */
    'requires' => array(
         'login' => '>=1.9.0',
    ),

    /* (optional) install.options is needed if you will interact
     * with user during the install.
     * See the user.input.php file for more information.
     * Set this to 'install.options' or ''
     * The file will be created as _build/install.options/user.input.php
     * Don't change the filename or directory name. */

   // 'install.options' => 'install.options',


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
        'CheckBoxes' => 'subscribe:checkboxes',
        'Unsubscribe' => 'subscribe:unsubscribe',
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
        'chunks',
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
        'Subscribe Folder',
        'Subscribe',
        'Manage Preferences',
        'Thank You for Registering',
        'Registration Confirmed',
        'ConfirmRegister',
        'Unsubscribe',
    ),
    /* Array of resource parent IDs to get children of. */
    'parents' => array(),
    /* Also export the listed parent resources
      (set to false to include just the children) */
    'includeParents' => false,


    /* ******************** LEXICON HELPER SETTINGS ***************** */
    /* These settings are used by LexiconHelper */
    'rewriteCodeFiles' => false,
    /*# remove ~~descriptions */
    'rewriteLexiconFiles' => false,
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
