<?php

$packageNameLower = 'fwa'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'Firewall Web App',
    /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'fwa project for Firewall Ministries',
    'version' => '1.0.1',
    'release' => 'pl',
    'author' => 'JM Addington',
    'email' => 'jm@jmaddington.com',
    'authorUrl' => 'http://www.jmaddington.com',
    'authorSiteName' => "JM Addington",
    'packageDocumentationUrl' => '',
    'copyright' => '2013',

    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'jmaddington',
    'gitHubRepository' => 'fwa',

    /* two-letter code of your primary language */
    'primaryLanguage' => 'en',

    /* Set directory and file permissions for project directories */
    'dirPermission' => 0777,
    /* No quotes!! */
    'filePermission' => 0666,
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
        'css_base_href' => array( // key
            'key' => 'fwa.css_base_href',
            'name' => 'Base CSS Directory',
            'description' => 'Path to prefix all CSS files with',
            'namespace' => 'fwa',
            'xtype' => 'textfield',
            'value' => '/assets/components/fwa/css/',
            'area' => 'HTML',
        ),
        'js_base_href' => array( // key
            'key' => 'fwa.js_base_href',
            'name' => 'JS Base Directory',
            'description' => 'Path to prefix all JS files with',
            'namespace' => 'fwa',
            'xtype' => 'textfield',
            'value' => '/assets/components/fwa/js/',
            'area' => 'HTML',
        ),
        'images_base_href' => array( // key
            'key' => 'fwa.images_base_href',
            'name' => 'Images Base Directory',
            'description' => 'Path to prefix all image files with',
            'namespace' => 'fwa',
            'xtype' => 'textfield',
            'value' => '/assets/components/fwa/images/',
            'area' => 'HTML',
        ),
        'fwa.stories_container' => array( // key
            'key' => 'fwa.stories_container',
            'name' => 'Stories container',
            'description' => 'ID of page that contains all the stories',
            'namespace' => 'fwa',
            'xtype' => 'textfield',
            'value' => '',
            'area' => 'Pages',
        ),
    ),

    /* ************************ NEW SYSTEM EVENTS ************************* */

    /* Array of your new System Events (not default
     * MODX System Events). Listed here so they can be created during
     * install and removed during uninstall.
     *
     * Warning: Do *not* list regular MODX System Events here !!! */

//    'newSystemEvents' => array(
//        'OnMyEvent1' => array(
//            'name' => 'OnMyEvent1',
//        ),
//        'OnMyEvent2' => array(
//            'name' => 'OnMyEvent2',
//            'groupname' => 'fwa',
//            'service' => 1,
//        ),
//    ),

    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'fwa' => array(
            'name' => 'fwa',
            'path' => '{core_path}components/fwa/',
            'assets_path' => '{assets_path}components/fwa/',
        ),

    ),

    /* ************************ CONTEXT(S) ************************* */
    /* (optional) List any contexts other than the 'web' context here
    */

//    'contexts' => array(
//        'fwa' => array(
//            'key' => 'fwa',
//            'description' => 'fwa context',
//            'rank' => 2,
//        )
//    ),

    /* *********************** CONTEXT SETTINGS ************************ */

    /* If your extra needs Context Settings, set their field values here.
     * You can also create or edit them in the Manager (Edit Context -> Context Settings),
     * and export them with exportObjects. If you do that, be sure to set
     * their namespace to the lowercase package name of your extra.
     * The context_key should be the name of an actual context.
     * */

//    'contextSettings' => array(
//        'fwa_context_setting1' => array(
//            'context_key' => 'fwa',
//            'key' => 'fwa_context_setting1',
//            'name' => 'fwa Setting One',
//            'description' => 'Description for setting one',
//            'namespace' => 'fwa',
//            'xtype' => 'textfield',
//            'value' => 'value1',
//            'area' => 'fwa',
//        ),
//        'fwa_context_setting2' => array(
//            'context_key' => 'fwa',
//            'key' => 'fwa_context_setting2',
//            'name' => 'fwa Setting Two',
//            'description' => 'Description for setting two',
//            'namespace' => 'fwa',
//            'xtype' => 'combo-boolean',
//            'value' => true,
//            'area' => 'fwa',
//        ),
//    ),

    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'fwa' => array(
            'category' => 'fwa',
            'parent' => '',
            /* top level category */
        ),
        'Stories' => array(
            'category' => 'Stories',
            'parent' => 'fwa',
            /* nested under fwa */
        )
    ),

    /* *************************** MENUS ****************************** */

    /* If your extra needs Menus, you can create them here
     * or create them in the Manager, and export them with exportObjects.
     * Be sure to set their namespace to the lowercase package name
     * of your extra.
     *
     * Every menu should have exactly one action */

//    'menus' => array(
//        'fwa' => array(
//            'text' => 'fwa',
//            'parent' => 'components',
//            'description' => 'ex_menu_desc',
//            'icon' => '',
//            'menuindex' => 0,
//            'params' => '',
//            'handler' => '',
//            'permissions' => '',
//
//            'action' => array(
//                'id' => '',
//                'namespace' => 'fwa',
//                'controller' => 'index',
//                'haslayout' => true,
//                'lang_topics' => 'fwa:default',
//                'assets' => '',
//            ),
//        ),
//    ),


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

        'propertySets' => array( /* all three fields are required */
            'storiesPageNav' => array(
                'name' => 'storiesPageNav',
                'description' => 'Property set for Stories page navigation',
                'category' => 'fwa',
            ),
        ),
//            'PropertySet2' => array(
//                'name' => 'PropertySet2',
//                'description' => 'Description for PropertySet2',
//                'category' => 'fwa',
//            ),
//        ),

//        'snippets' => array(
//            'Snippet1' => array(
//                'category' => 'fwa',
//                'description' => 'Description for Snippet one',
//                'static' => true,
//            ),
//
//            'Snippet2' => array( /* fwa with static and property set(s)  */
//                'category' => 'Category2',
//                'description' => 'Description for Snippet two',
//                'static' => false,
//                'propertySets' => array(
//                    'PropertySet1',
//                    'PropertySet2'
//                ),
//            ),
//
//        ),
//        'plugins' => array(
//            'Plugin1' => array( /* minimal fwa */
//                'category' => 'fwa',
//            ),
//            'Plugin2' => array( /* fwa with static, events, and property sets */
//                'category' => 'fwa',
//                'description' => 'Description for Plugin one',
//                'static' => false,
//                'propertySets' => array( /* all property sets to be connected to element */
//                    'PropertySet1',
//                ),
//                'events' => array(
//                    /* minimal fwa - no fields */
//                    'OnUserFormSave' => array(),
//                    /* fwa with fields set */
//                    'OnMyEvent1' => array(
//                        'priority' => '0', /* priority of the event -- 0 is highest priority */
//                        'group' => 'plugins', /* should generally be set to 'plugins' */
//                        'propertySet' => 'PropertySet1', /* property set to be used in this pluginEvent */
//                    ),
//                    'OnMyEvent2' => array(
//                        'priority' => '3',
//                        'group' => 'plugins',
//                        'propertySet' => '',
//                    ),
//                    'OnDocFormSave' => array(
//                        'priority' => '4',
//                        'group' => 'plugins',
//                        'propertySet' => '',
//                    ),
//
//
//                ),
//            ),
//        ),
        'chunks' => array(
            'headTag' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Head tag common to all FWA pages'
            ),
            'headerTag' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Header tag common to all FWA pages'
            ),
            'footerTag' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Footer tag common to all FWA pages'
            ),
            'eodJS' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'End of document javascript common to all pages'
            ),
            'ga' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Google Analytics code'
            ),
            'fwaBackButton' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Back button for pages'
            ),
            'fwaStoryBackground' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Style tag for background image on story/prayer page'
            ),
            'fwaStoriesList' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Chunk used to wrap Story listings on Stories page'
            ),
            'fwaStoryTemplate' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Templates the story layout on individual story pages and the Pray Now page'
            ),
            'fwaStoryJS' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'Javascript at the end of each story'
            ),
            'fwaStoriesPageNavOuterTpl' => array(
                'category' => 'fwa',
                'static' => true,
                'description' => 'A content tpl for controlling the layout of the various page navigation controls.'
            ),
        ),
//            'Chunk2' => array(
//                'description' => 'Description for Chunk two',
//                'category' => 'fwa',
//                'static' => false,
//                'propertySets' => array(
//                    'PropertySet2',
//                ),
//            ),
//        ),
        'templates' => array(
            'Story' => array(
                'category' => 'fwa',
                'description' => 'FWA Story Page',
                'static' => true,
            ),
            'Home Page' => array(
                'category' => 'fwa',
                'description' => 'FWA Home Page',
                'static' => true,
            ),
            'Generic Sub Page' => array(
                'category' => 'fwa',
                'description' => 'FWA Generic Subpage',
                'static' => true,
            ),
            'FWA Stories Page' => array(
                'category' => 'fwa',
                'description' => 'FWA page that lists all stories',
                'static' => true,
            ),
            'FWA Pray Now Page' => array(
                'category' => 'fwa',
                'description' => 'FWA page that shows a single prayer at a time',
                'static' => true,
            ),
            'FWA Submit Tip Page' => array(
                'category' => 'fwa',
                'description' => 'FWA page to allow users to submit an HT tip',
                'static' => true,
            ),
        ),
        'templateVars' => array(
            'Verse' => array(
                'category' => 'Stories',
                'description' => 'Verse that goes with this story',
                'caption' => 'Verse',
                'templates' => array(
                    'Story' => 1,
                ),
            ),
            'Prayer' => array(
                'category' => 'Stories',
                'description' => 'Prayer that goes with this story',
                'caption' => 'Prayer',
                'templates' => array(
                    'Story' => 2,
                ),
            ),
            'Image' => array(
                'category' => 'Stories',
                'description' => 'Image that goes with this story',
                'caption' => 'Image',
                'templates' => array(
                    'Story' => 3,
                ),
            ),
            'Age' => array(
                'category' => 'Stories',
                'description' => 'Age for this story',
                'caption' => 'Age',
                'templates' => array(
                    'Story' => 4,
                ),
            ),
            'Region' => array(
                'category' => 'Stories',
                'description' => 'Region for this story',
                'caption' => 'Region',
                'templates' => array(
                    'Story' => 5,
                ),
            ),
            'SlaveryType' => array(
                'category' => 'Stories',
                'description' => 'Slavery type for this story',
                'caption' => 'Slavery Type',
                'templates' => array(
                    'Story' => 6,
                ),
            ),

            'fwaStoryBackground' => array(
                'category' => 'Stories',
                'description' => 'Background layer between text and image',
                'caption' => 'Background',
                'templates' => array(
                    'Story' => 1,
                ),
            ),

            'fwaPhotoCopyright' => array(
                'category' => 'Stories',
                'description' => 'Copyright text to appear for this background photo',
                'caption' => 'Photo copyright',
                'templates' => array(
                    'Story' => 1,
                ),
            ),

            'fwaSchedulingMIGX' => array(
                'category' => 'fwa',
                'description' => '',
                'caption' => 'Use this to schedule prayers day by day',
                'templates' => array(
                    'FWA Pray Now Page' => 1,
                ),
            ),

//            'Tv2' => array( /* fwa with templates, default, and static specified */
//                'category' => 'fwa',
//                'description' => 'Description for TV two',
//                'caption' => 'TV Two',
//                'static' => false,
//                'default_text' => '@INHERIT',
//                'templates' => array(
//                    'default' => 3, /* second value is rank -- for ordering TVs when editing resource */
//                    'Template1' => 4,
//                    'Template2' => 1,
//                ),
//            ),
        ),
    ),
    /* (optional) will make all element objects static - 'static' field above will be ignored */
    'allStatic' => true,


    /* ************************* RESOURCES ****************************
     Important: This list only affects Bootstrap. There is another
     list of resources below that controls ExportObjects.
     * ************************************************************** */
    /* Array of Resource pagetitles for your Extra; All other fields optional.
       You can set any resource field here */
//    'resources' => array(
//        'Resource1' => array( /* minimal fwa */
//            'pagetitle' => 'Resource1',
//            'alias' => 'resource1',
//            'context_key' => 'fwa',
//        ),
//        'Resource2' => array( /* fwa with other fields */
//            'pagetitle' => 'Resource2',
//            'alias' => 'resource2',
//            'context_key' => 'fwa',
//            'parent' => 'Resource1',
//            'template' => 'Template2',
//            'richtext' => false,
//            'published' => true,
//            'tvValues' => array(
//                'Tv1' => 'SomeValue',
//                'Tv2' => 'SomeOtherValue',
//            ),
//        )
//    ),


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
    'hasAssets' => true,
    'minifyJS' => true,
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
        'changelog.txt',
    ),

    /* (optional) Description file for GitHub project home page */
    'readme.md' => false,
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
//        'addUsers'
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
        'fwa' => 'fwa:fwa',
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
        'contexts',
        'snippets',
        'plugins',
        'templateVars',
        'templates',
        'chunks',
        'resources',
        'propertySets',
        'systemSettings',
        'contextSettings',
//        'systemEvents',
//        'menus'
    ),
    /*  Array  of resources to process. You can specify specific resources
        or parent (container) resources, or both.

        They can be specified by pagetitle or ID, but you must use the same method
        for all settings and specify it here. Important: use IDs if you have
        duplicate pagetitles */
    'getResourcesById' => false,

    'exportResources' => array(
        'Submit Tip',
//        'Pray Now',
    ),
    /* Array of resource parent IDs to get children of. */
    'parents' => array(//        'Connect',
//            'Prayer',
//                'Pray-111',
//                    'Join the Team',
//                        'Pray for Firewall Ministries',
//                        'Be A Voice',
//                'Pray Now',
//            'What is HT?',
//        'Resources',
////            'Stories',
//            'Stats',
//                'Statistics',
//                    'Regional',
//                        'North America',
//                            'United States',
//                                'Tennessee'

    ),
    /* Also export the listed parent resources
      (set to false to include just the children) */
    'includeParents' => true,


    /* ******************** LEXICON HELPER SETTINGS ***************** */
    /* These settings are used by LexiconHelper */
    'rewriteCodeFiles' => false,
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