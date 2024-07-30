<?php

$packageNameLower = 'showtv'; /* No spaces, no dashes */

$components = array(
    /* These are used to define the package and set values for placeholders */
    'packageName' => 'ShowTv',  /* No spaces, no dashes */
    'packageNameLower' => $packageNameLower,
    'packageDescription' => 'ShowTv project for MyComponent extra',
    'version' => '1.1.3',
    'release' => 'pl',
    'author' => 'Bob Ray',
    'email' => '<https://bobsguides.com>',
    'authorUrl' => 'https://bobsguides.com',
    'authorSiteName' => "Bob's Guides",
    'packageDocumentationUrl' => 'https://bobsguides.com/showtv-tutorial.html',
    'copyright' => '2012-2024',

    /* no need to edit this except to change format */
    'createdon' => strftime('%m-%d-%Y'),

    'gitHubUsername' => 'BobRay',
    'gitHubRepository' => 'ShowTv',

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


    /* ************************ NAMESPACE(S) ************************* */
    /* (optional) Typically, there's only one namespace which is set
     * to the $packageNameLower value. Paths should end in a slash
    */

    'namespaces' => array(
        'showtv' => array(
            'name' => 'showtv',
            'path' => '{core_path}components/showtv/',
            'assets_path' => '{assets_path}components/showtv/',
        ),

    ),


    /* ************************* CATEGORIES *************************** */
    /* (optional) List of categories. This is only necessary if you
     * need to categories other than the one named for packageName
     * or want to nest categories.
    */

    'categories' => array(
        'ShowTv' => array(
            'category' => 'ShowTv',
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
            'ShowTv' => array(
                'category' => 'ShowTv',
                'description' => 'Show the value of any TV, anywhere',
                'static' => false,
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
    'resources' => array(),


    /* Array of languages for which you will have language files,
     *  and comma-separated list of topics
     *  ('.inc.php' will be added as a suffix). */
    'languages' => array(
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
    ),

    /* (optional) Validators can abort the install after checking
     * conditions. Array of validator names (no
     * prefix of suffix) or '' 'default' creates a default resolver
     *  named after the package suffix 'validator.php' will be added */

    'validators' => array(
    ),


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
    ),
    /*  Array  of resources to process. You can specify specific resources
        or parent (container) resources, or both.

        They can be specified by pagetitle or ID, but you must use the same method
        for all settings and specify it here. Important: use IDs if you have
        duplicate pagetitles */
    'getResourcesById' => false,

    'exportResources' => array(
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
