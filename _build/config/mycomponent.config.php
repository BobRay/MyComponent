<?php

$packageNameLower = 'mycomponent'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'MyComponent',
    /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'MyComponent MODX Extra development tool',
    'version' => '3.2.2',
    'release' => 'pl',
    'author' => 'Bob Ray',
    'email' => '<http://bobsguides.com>',
    'authorUrl' => 'http://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'http://bobsguides.com/mycomponent-tutorial.html',
    'copyright' => '2012-2014',
    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'MyComponent',

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Set directory and file permissions for project directories */
    'dirPermission' => 0755,
    /* No quotes!! */
    'filePermission' => 0644,
    /* No quotes!! */

    /* Define source and target directories (mycomponent root and core directories) */
    'mycomponentRoot' => $this->modx->getOption('mc.root', null,
        MODX_CORE_PATH . 'components/mycomponent/'),

    /* path to new project root */
    'targetRoot' => MODX_ASSETS_PATH . 'mycomponents/mycomponent2/',


    /* *********************** NEW SYSTEM SETTINGS ************************ */

    /* If your extra needs new System Settings, set their field values here.
     * You can also create or edit them in the Manager (System -> System Settings),
     * and export them with exportObjects. If you do that, be sure to set
     * their namespace and area to the lowercase package name of your extra */

    /*'newSystemSettings' => array(
        'example_system_setting1' => array( // key
            'key' => 'example_system_setting1',
            'name' => 'Example Setting One',
            'description' => 'Description for setting one',
            'namespace' => 'example',
            'xtype' => 'textfield',
            'value' => 'value1',
            'area' => 'area1',
        ),
        'example_system_setting2' => array( // key
            'key' => 'example_system_setting2',
            'name' => 'Example Setting Two',
            'description' => 'Description for setting two',
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

    /*'newSystemEvents' => array(
        'OnMyEvent1' => array(
            'name' => 'OnMyEvent1',
        ),
        'OnMyEvent2' => array(
            'name' => 'OnMyEvent2',
            'groupname' => 'Example',
            'service' => 1,
        ),
    ),*/

    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */ 

    'namespaces' => array(
        'example' => array(
            'name' => 'mycomponent',
            'path' => '{core_path}components/mycomponent2/',
            'assets_path' => '{assets_path}components/mycomponent2/',
        ),

    ),

    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'MyComponent' => array(
            'category' => 'MyComponent',
            'parent' => '',
            /* top level category */
        ),
    ),

    /* *************************** MENUS ****************************** */

    /* If your extra needs Menus, set this to true, create them
     * in the Manager, and export them with exportObjects. Be sure
     * to set their namespace to the lowercase package name of your extra */
    'menus' => array(),

    /* ************************* ELEMENTS **************************** */

    /* Array containing elements for your extra. 'category' is required
       for each element, all other fields are optional.
       Property Sets (if any) must come first! */


    'elements' => array(
        'snippets' => array(
            'Build' => array(
                'category' => 'MyComponent',
                'filename' => 'build.php',
            ),
            'MyComponent' => array(
                'category' => 'MyComponent',
                'filename' => 'mycomponent.php',
            ),
            'Bootstrap' => array(
                'category' => 'MyComponent',
                'filename' => 'bootstrap.php',
            ),
            'ExportObjects' => array(
                'category' => 'MyComponent',
                'filename' => 'exportobjects.php',
            ),
            'LexiconHelper' => array(
                'category' => 'MyComponent',
                'filename' => 'lexiconhelper.php',
            ),
            'CheckProperties' => array(
                'category' => 'MyComponent',
                'filename' => 'checkproperties.php',
            ),
            'ImportObjects' => array (
                'category' => 'MyComponent',
                'filename' => 'importobjects.php',
            ),
            'RemoveObjects' => array (
                'category' => 'MyComponent',
                'filename' => 'removeobjects.php',
            ),

        ),
        'chunks' => array(
            'build.config.php' => array(
                'category' => 'MyComponent',
                'filename' => 'build.config.php',
            ),
            'build.transport.php' => array(
                'category' => 'MyComponent',
                'filename' => 'build.transport.php',
            ),
            'categoryresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'categoryresolver.php',
            ),
            'changelog.txt.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'changelog.txt.tpl',
            ),
            'classfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'classfile.php',
            ),
            'cmp.actionfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.actionfile.php',
            ),
            'cmp.changecategory.class.php' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.changecategory.class.php',
            ),
            'cmp.classfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.classfile.php',
            ),
            'cmp.connectorfile.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.connectorfile.tpl',
            ),
            'cmp.controllerhome.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.controllerhome.tpl',
            ),
            'cmp.defaultjs.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.defaultjs.tpl',
            ),
            'cmp.getlist.class.php' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.getlist.class.php',
            ),
            'cmp.grid.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.grid.tpl',
            ),
            'cmp.home.js.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.home.js.tpl',
            ),
            'cmp.home.panel.js.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.home.panel.js.tpl',
            ),
            'cmp.mgr.css.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.mgr.css.tpl',
            ),
            'cmp.processor.class.php' => array(
                'category' => 'MyComponent',
                'filename' => 'cmp.processor.class.php',
            ),
            'css.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'css.tpl',
            ),
            'example.config.php' => array(
                'category' => 'MyComponent',
                'filename' => 'example.config.php',
            ),
            'genericresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'genericresolver.php',
            ),
            'genericvalidator.php' => array(
                'category' => 'MyComponent',
                'filename' => 'genericvalidator.php',
            ),
            'js.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'js.tpl',
            ),
            'license.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'license.tpl',
            ),
            'license.txt.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'license.txt.tpl',
            ),
            'modchunk.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'modchunk.tpl',
            ),
            'modresource.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'modresource.tpl',
            ),
            'modtemplate.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'modtemplate.tpl',
            ),
            'mycomponentform.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'mycomponentform.tpl',
            ),
            'phpfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'phpfile.php',
            ),
            'pluginresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'pluginresolver.php',
            ),
            'propertiesfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'propertiesfile.php',
            ),
            'propertysetresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'propertysetresolver.php',
            ),
            'readme.md.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'readme.md.tpl',
            ),
            'readme.txt.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'readme.txt.tpl',
            ),
            'removenewevents.php' => array(
                'category' => 'MyComponent',
                'filename' => 'removenewevents.php',
            ),
            'resourceresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'resourceresolver.php',
            ),
            'transportfile.php' => array(
                'category' => 'MyComponent',
                'filename' => 'transportfile.php',
            ),
            'tutorial.html.tpl' => array(
                'category' => 'MyComponent',
                'filename' => 'tutorial.html.tpl',
            ),
            'tvresolver.php' => array(
                'category' => 'MyComponent',
                'filename' => 'tvresolver.php',
            ),
            'user.input.php' => array(
                'category' => 'MyComponent',
                'filename' => 'user.input.php',
            ),
        ),

        'templates' => array(
            'MyComponentTemplate' => array(
                'category' => 'MyComponent',
                'filename' => 'mycomponenttemplate.html',
            ),
        )

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
        'MyComponent' => array( /* minimal example */
            'pagetitle' => 'MyComponent',
            'alias' => 'mycomponent',
            'longtitle' =>  'My Component Control Center',
            'published' => '0',
            'hidemenu' => '1',
            'richtext' => '0',
            'template' => 'MyComponentTemplate',

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

    'validators' => array(

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
        'ActionAdapter' => 'actionadapter',
        'CategoryAdapter' => 'categoryadapter',
        'CheckProperties' => 'checkproperties',
        'ChunkAdapter' => 'chunkadapter',
        'ContextAdapter' => 'contextadapter',
        'ContextSettingAdapter' => 'contextsettingadapter',
        'ElementAdapter' => 'elementadapter',
        'Helpers' => 'helpers',
        'JSMin' => 'jsmin',
        'JSMinPlus' => 'jsminPlus',
        /* 'mc_auto_load' => 'mcautoload', */
        'LexiconCodeFile' => 'lexiconcodefile',
        'LexiconHelper' => 'lexiconhelper',
        'MenuAdapter' => 'menuadapter',
        'MyComponentProject' => 'mycomponentproject',
        'NamespaceAdapter' => 'namespaceadapter',
        'ObjectAdapter' => 'objectadapter',
        'PluginAdapter' => 'pluginadapter',
        'PropertySetAdapter' => 'propertysetadapter',
        'ResourceAdapter' => 'resourceadapter',
        'SnippetAdapter' => 'snippetadapter',
        'SubpackageAdapter' => 'subpackageadapter',
        'SystemEventAdapter' => 'systemeventadapter',
        'SystemSettingAdapter' => 'systemsettingadapter',
        'TemplateAdapter' => 'templateadapter',
        'TemplateVarAdapter' => 'templatevaradapter',
        'UserGroupAdapter' => 'usergroupadapter',
        'UserGroupRoleAdapter' => 'usergrouproleadapter',
        'UserSettingAdapter' => 'usersettingadapter',

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
    ),
    /*  Array  of resources to process. You can specify specific resources
        or parent (container) resources, or both.

        They can be specified by pagetitle or ID, but you must use the same method
        for all settings and specify it here. Important: use IDs if you have
        duplicate pagetitles */
    'getResourcesById' => false,

    'exportResources' => array(
        'MyComponent',
    ),

    /* Array of resource parent IDs to get children of. */
    'parents' => array(),
    /* Also export the listed parent resources
      (set to false to include just the children) */
    'includeParents' => false,


    /* ******************** LEXICON HELPER SETTINGS ***************** */
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