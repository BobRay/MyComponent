<?php
// Include the Base Class (only once)
require_once('elementadapter.class.php');

class CategoryAdapter extends ElementAdapter
{//These will never change.
    final static protected $xPDOClass = 'modCategory';
    final static protected $xPDOTransportAttributes = array
    (   xPDOTransport::UNIQUE_KEY => 'category',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
        
    );
    
// Database Columns for the XPDO Object
    protected $myColumns;

    private myTemplates = false;
    private myChunks = false;
    private mySnippets = false;
    private myPlugins = false;
    private myTemplateVariables;
  
    final public function __construct(&$forComponent, $columns)
    {   parent::__construct(&$forComponent);
        if (is_array($columns))
            $this->myColumns = $columns;
    }
    
    final public function buildVehicle()
    {//Localize builder
        $builder =& $useBuilder;
        if (!empty($this->properties)
        ||  is_array($this->properties))
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Resource has no Properties');
            return false;
        }
    //For quick access
        $attr = $this->attr;
        $title = $this->properties['pagetitle'];
    // We must have Attributes in order to Package
        if (empty($attr)
        ||  is_array($attr))
        {   $myComponent->log(modX::LOG_LEVEL_ERROR, 'Could not package Category: '.$title);
            return false;
        }
    // Add to the Transport Package
        if (parent::buildVehicle())
        {//Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Category: '.$title);
            return true;
        }
        else
            return false;
        
        /* create category  The category is required and will automatically
         * have the name of your package
         */
        /* @var $category modCategory */
        $category= $modx->newObject('modCategory');
        $category->set('id',1);
        $category->set('category',PKG_CATEGORY);
        
        /* add snippets */
        if ($hasSnippets) {
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Snippets.');
            $snippets = include $sources['data'].'transport.snippets.php';
            /* note: Snippets' default properties are set in transport.snippets.php */
            if (is_array($snippets)) {
                $category->addMany($snippets, 'Snippets');
            } else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding Snippets failed.'); }
        }
        /* ToDo: Implement Property Sets */
        if ($hasPropertySets) {
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Property Sets.');
            $propertySets = include $sources['data'].'transport.propertysets.php';
            //  note: property set' properties are set in transport.propertysets.php
            if (is_array($propertySets)) {
                $category->addMany($propertySets, 'PropertySets');
            } else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding Property Sets failed.'); }
        }
        if ($hasChunks) { /* add chunks  */
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Chunks.');
            /* note: Chunks' default properties are set in transport.chunks.php */    
            $chunks = include $sources['data'].'transport.chunks.php';
            if (is_array($chunks)) {
                $category->addMany($chunks, 'Chunks');
            } else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding Chunks failed.'); }
        }
        
        
        if ($hasTemplates) { /* add templates  */
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Templates.');
            /* note: Templates' default properties are set in transport.templates.php */
            $templates = include $sources['data'].'transport.templates.php';
            if (is_array($templates)) {
                if (! $category->addMany($templates,'Templates')) {
                    $modx->log(modX::LOG_LEVEL_INFO,'addMany failed with templates.');
                };
            } else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding Templates failed.'); }
        }
        
        if ($hasTemplateVariables) { /* add template variables  */
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Template Variables.');
            /* note: Template Variables' default properties are set in transport.tvs.php */
            $tvs = include $sources['data'].'transport.tvs.php';
            if (is_array($tvs)) {
                $category->addMany($tvs, 'TemplateVars');
            } else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding Template Variables failed.'); }
        }
        
        
        if ($hasPlugins) {
            $modx->log(modX::LOG_LEVEL_INFO,'Adding in Plugins.');
            $plugins = include $sources['data'] . 'transport.plugins.php';
             if (is_array($plugins)) {
                $category->addMany($plugins);
             } else {
                 $modx->log(modX::LOG_LEVEL_FATAL, 'Adding Plugins failed.');
             }
        }
        
        /* Create Category attributes array dynamically
         * based on which elements are present
         */
        
        if ($hasValidator) {
              $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
        }
        
        if ($hasSnippets) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                );if ($validator == 'default') {
        }
        
        if ($hasPropertySets) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['PropertySets'] = array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                );
        }
        
        if ($hasChunks) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                );
        }
        
        if ($hasPlugins) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            );
        }
        
        if ($hasTemplates) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'templatename',
            );
        }
        
        if ($hasTemplateVariables) {
            $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['TemplateVars'] = array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
            );
        }
        
        parent::buildVehicle();
        
       
        /* Package in script resolvers, if any */
        
        $resolvers = empty($props['resolvers'])? array() : explode(',', $props['resolvers']);
        $resolvers = array_merge($resolvers, array('plugin','tv','resource','propertyset'));
        
        /* This section transfers every file in the local
         mycomponents/mycomponent/assets directory to the
         target site's assets/mycomponent directory on install.
         If the assets dir. has been renamed or moved, they will still
         go to the right place.
         */
        
        if ($hasCore) {
            $vehicle->resolve('file', array(
                    'source' => $sources['source_core'],
                    'target' => "return MODX_CORE_PATH . 'components/';",
                ));
        }
        
        /* This section transfers every file in the local 
         mycomponents/mycomponent/core directory to the
         target site's core/mycomponent directory on install.
         If the core has been renamed or moved, they will still
         go to the right place.
         */
        
            if ($hasAssets) {
                $vehicle->resolve('file',array(
                    'source' => $sources['source_assets'],
                    'target' => "return MODX_ASSETS_PATH . 'components/';",
                ));
            }
        
        /* Add subpackages */
        /* The transport.zip files will be copied to core/packages
         * but will have to be installed manually with "Add New Package and
         *  "Search Locally for Packages" in Package Manager
         */
        
        if ($hasSubPackages) {
            $modx->log(modX::LOG_LEVEL_INFO, 'Adding in subpackages.');
             $vehicle->resolve('file',array(
                'source' => $sources['packages'],
                'target' => "return MODX_CORE_PATH;",
                ));
        }
    }
}