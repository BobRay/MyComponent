## Component

This structure focuses around the Component object. The Component serves as the parent for all of the other classes in this directory. The Component Class has 4 major public methods: New(), Put(), Get(), Build(). These methods are top-down, running the methods in the same name in all child objects. 

### Core Methods

#### New()
Creates the File Hierarchy from the initial Configuration. This is run by BootStrap.

#### Put($overwrite = false)
Put copies the File Hierarchy to the MODx Installation. Put is generally only be called when BootStrap is run. In these cases, Put() is run immediately after New(). The existence of Put() also allows for a new utility, Import. By default, Put() is nondestructive, but if $overwrite is set to true, it will rewrite the current MODx objects.

#### Get($overwrite = false)
Get copies the MODx objects in the Namespace and Categories to the Component's File Hierarchy. This may only be run by ExportObjects. By default, Get() is nondestructive, however, if $overwrite is set to true, it will overwrite the current File Hierarchy.

#### Build() 
Compiles the File Heirarchy into a Transport Package. This may only be run by the Component's Build.Transport.php. 