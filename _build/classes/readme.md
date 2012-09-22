## MyComponentProject

This structure focuses around the MyComponentProject object. The MyComponentProject serves as the parent for all of the other classes in this directory. MyComponentProject contains the majority of the functionality of BootStrap.class, ExportObjects.class and Build.Transport.php.

### Public Methods
These are the methods that are called by the localized utilities. When run, these utilities instance the MyComponentProject. MyComponentProject finds its nearby project.config file, loads it, and then connects to MODx. The localized utility then calls one of these public methods. 

#### newProject()
Creates the File Hierarchy from the initial Configuration. This is run by BootStrap.

#### importProject($overwrite = false)
Translates the objects in the File System into the MODx Installation. importProject is generally only be called when BootStrap is run. In these cases, importProject() is run immediately after newProject(). The existence of importProject() also allows for a new utility, Import. By default, importProject() is nondestructive, but if $overwrite is set to true, it will rewrite the current MODx objects.

#### exportProject($overwrite = false)
Get copies the MODx objects in the Namespace and Categories to the Project's File Hierarchy. This may only be run by the Project's ExportObjects. By default, exportProject() is nondestructive, however, if $overwrite is set to true, it will overwrite the current File Hierarchy.

#### buildPackage() 
Compiles the File Heirarchy into a Transport Package. This may only be run by the Project's Build.Transport.php. 

## MODxObjectAdapter

Most other classes in this structure extend the MODxObjectAdapter. This is where, for many, the top-down methods are contained, unless a specific subclass requires additional functionality. In essence, a Vehicle is just an PHP array containing all of the properties of a MODx object.

Like MyComponentProject, MODxObjectAdapter will have 4 similar primary methods. These may only be called by an object that is a child of MyComponentProject. MyComponentProject, however, does not have to know the organizational structure, below its immediate children, and vice versa. 

### Public Methods

#### newTransport()
This creates a new Transport File based upon the object's properties as determined by the project.config. ResourceAdapter and all ElementAdapters additionally create a Code File after the Transport File has been successfully made.

#### addToMODx($overwrite = false)
Translates the Object in the defined Tranport File into the MODx Installation as a MODx Object. If the MODx Object has linked dependencies, this is handled by the appropriate Adapter. One special circumstance is the ResourceAdapter, which sets defaults for new Resources being added. In this circumstance, it sets these values prior to calling the parent::addToMODx.

#### exportObject($overwrite = false)
Gets the MODx Object from MODx based on its $xPDOClass, $xPDOClassNameKey, and its $myColumns[$xPDOClassNameKey] value. If the object exists, it copies its column values to the Transport File. If the Object happens to be a ResourceAdapter or any extension of an ElementAdapter, it will also copy the code to its appropriate code file.

#### buildTransport() 
Translates the current object into a Transport Vehicle for a MODx Transport Package. Attributes are set to the Vehicle according to $xPDOTransportAttributes. This works well because in nearly every circumstance, the attributes do not change between different instances of the Adapters, just between the Adapter types themselves. In most cases, this will result in the creation of a Vehicle. Linking to the appropriate objects (category, etc) should already be complete at this point, so these should resolve correctly without any "tweaking".

### Subclasses
Most of the subclasses of MODxObjectAdapter are going to be "shallow" extensions. This allows the base code in MODxObjectAdapter to do most of the work, but gives the object the opportunity to preprocess or postprocess the default actions. In very few cases, like NamespaceAdapter, it may override the behavior completely. *(For instance, namespaces use registerNamespace() rather than createVehicle() during buildTransport())*

## Differences from core MyComponent

### Global Utilities vs. Localized Utilities
The Global Utilities will ultimately serve as "routers", that way they can be called by MODx, the UI or by command-line. This will reduce the amount of re-learning that must be accomplished by an older user. If the Global Utility sent with a project name, it will look for the project name in the File System. Failing that, it will check the build.config to see which project it was working with. This way, there is no requirement to adjust the build.config. This ultimately takes advantage of the fact that there is already an inherent structure in place.

### Changes to Packages
Unlike the current version of MyComponent, each object goes into its own Vehicle. This reduces the need for Resolvers that must reconcile links, for instance, like Plugins to Events. Due to the nature of the MODx Package Installer, as long as the objects reference the correct Namespace or Category, they will automatically be added accordingly. This mitigates the need (currently) for addOne() and addMany().

### Changes to File/Directory Structure
The file and directory hierarchy largely remains the same. Some key differences are that each object creates its own transport file (as it is currently built). This may change (and be changed) quite quickly and easily. However, until stability is confirmed, this is the direction that it is going. Code files filenames remain unchanged.

The resultant filename is:
    'transport.' . static::$xPDOClass . '.' . $this->myColumns[$xPDOClassNameKey] . '.php';
    
In other words, a Snippet named DoMyStuff would be in the file:
    'transport.modsnippet.domystuff.php'

The result is smaller files, more easily read for large, involved packages. They are automatically sorted and easily "walked". Additionally they are much easier to edit. Small projects feel a slight burden, and group edits are impossible. **Note:** The above file name is not set in stone and may be adjusted very, very easily on a MyComponent wide basis.

## Propositions

### Templates for new Objects

MyComponent may gain additional benefits by adjusting the templatized base code filenames. For instance, since every object has its xPDOClass, we can now find the template using the following formula, eliminating the need for adding/removing the prepending 'mod':

    'basic.' . static::$xPDOClass . '.tpl';
    
Overrides may be performed by prepeding 'my.' in front of the name. As in:

    'my.basic.' . static::$xPDOClass . '.tpl';
    
Even further specific overrides may be performed by prepending the package name. As in:

    'packagename.basic.' . static::$xPDOClass . '.tpl';

This prioritized kind of templating allows users to choose their own generic templates, but even meet specific needs of specific projects. In other words, we check for a package specific template, first. Failing that, we look for a user specific template. Finally, if neither is present, we always have the default basic template.

## Still Adding...

* *Known Classes*
  * PropertySetAdapter.class.php
  * PackageAdapter.class.php - for sub-packages
* *Missing Functionality*
  * Schema handling
  * Copying localized classes
  * Copying localized utilities