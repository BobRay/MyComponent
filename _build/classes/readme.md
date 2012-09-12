
## Component

This structure focuses around the Component object. The Component serves as the parent for all of the other classes in this directory. Component contains the majority of the functionality of BootStrap, ExportObjects and BuildTransport.

The Component Class has 4 major public methods: New(), Put(), Get(), Build(). These methods are top-down, running the methods in the same name in all child objects. 

### Core Methods

#### New()
Creates the File Hierarchy from the initial Configuration. This is run by BootStrap.

#### Put($overwrite = false)
Put copies the File Hierarchy to the MODx Installation. Put is generally only be called when BootStrap is run. In these cases, Put() is run immediately after New(). The existence of Put() also allows for a new utility, Import. By default, Put() is nondestructive, but if $overwrite is set to true, it will rewrite the current MODx objects.

#### Get($overwrite = false)
Get copies the MODx objects in the Namespace and Categories to the Component's File Hierarchy. This may only be run by ExportObjects. By default, Get() is nondestructive, however, if $overwrite is set to true, it will overwrite the current File Hierarchy.

#### Build() 
Compiles the File Heirarchy into a Transport Package. This may only be run by the Component's Build.Transport.php. 

## ComponentVehicle

Most other classes in this structure extend the ComponentVehicle. This is where, for many, the top-down methods are contained, unless a specific subclass requires additional functionality. In essence, a Vehicle is just an PHP array containing all of the properties of a MODx object.

Like Component, ComponentVehicle will have the same 4 primary methods. These may only be called by an object that is a child of Component. Component, however, does not  have to know the organizational structure, below its immediate children, and vice versa. 

### Subclasses
Most of the subclasses of ComponentVehicle are going to be "shallow" extensions. This allows the base code to do most of the work, but gives the object the opportunity to override.

#### Classes that extend ComponentVehicle
* ComponentResource
* ComponentChunk (via ComponentElement)
* ComponentTemplate (via ComponentElement)
* ComponentSnippet (via ComponentElement)
* ComponentPlugin (via ComponentElement)

### Notes on "Submission"
It has been advised to me that addMany() doesn't always seem to work right. As such, this component will focus on looped use of addOne().