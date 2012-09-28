## Proposal of Submission

This is a supplemental refactor of the already in place functionality of MyComponent. The purpose of this supplement is to keep the functionality in place, but reduce the previously necessary duplication of effort. In actuality, bootstrap, exportobjects, and build.transport all do the same job, but in different directions.

By abstracting this out just a bit, they can all become shallow uni-directional functions and serve the same purpose. This allows us to create even shallower copies that reference the actual MyComponent's utilities, but allow further separation.

### Reason for Refactor

Once the UI is built, there will have to be "smaller" steps. Currently, for command-line/url functionality, this requires pre-purposing. GUI will not allow for full pre-purposing, in this manner. A submission form via AJAX and a Custom Manager Page will be necessary. Only after the submission will the bootstrap be able to do its work.

### What remains consistent?

Nearly all of the functionality will not change in any way. Movement of functions into classes will optimize this process. The classes themselves are omni-directional. Most are simply extensions of the core ComponentVehicle and will have the same functionality, so that we are not reproducing the same lines over and over again.

### Key Differences

#### newcomponent.config.php

This file will be copied to the new component's _build directory. This will make it a fully independent.

#### Utilities

Shallow "pointer" copies will be added to the _build directory under "utilities". This keeps command-line/url fully functional, and allows the GUI to work without changing "workspaces".

The real utilities (for the UI), whereever they end up, will actually call the classes and force the directions. In other words, the classes are not functional without the bootstrap, utilities, or build.transport to use them. They do not have a $modx object without being given one, and therefore cannot do anything unless told.

### How does this change MyComponent?

It does not. MyComponent's core code does not have to change, if it does not want. And the UI can remain completely independent. If MyComponent would benefit from this, nothing really changes anyway, since the classes use all of its code it the first place.