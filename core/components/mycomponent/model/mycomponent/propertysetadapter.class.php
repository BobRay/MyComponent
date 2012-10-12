<?php

class PropertySetAdapter extends ElementAdapter { //This will never change.
    protected $dbClass = 'modPropertySet';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/propertyset/create';
    protected $updateProcessor = 'element/propertyset/update';

// Database fields for the XPDO Object
    protected $myFields;
    protected $name;

    final function __construct(&$modx, &$helpers, $fields) {
        $this->name = $fields['name'];
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
        parent::__construct($modx, $helpers);

    }

    /* *****************************************************************************
       Bootstrap and Support Functions (in ElementAdapter)
    ***************************************************************************** */

    public static function createResolver($dir, $intersects, $helpers) {

        /* Create tv.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(MODX::LOG_LEVEL_INFO, 'Creating elementPropertySet resolver');
            $tpl = $helpers->getTpl('propertysetresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(MODX::LOG_LEVEL_ERROR, 'propertysetresolver tpl is empty');
                return false;
            }

            $fileName = 'propertyset.resolver.php';

            if (!file_exists($dir . '/' . $fileName)) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);

                $helpers->writeFile($dir, $fileName, $tpl);
            } else {
                $helpers->sendLog(MODX::LOG_LEVEL_INFO, '    ' . $fileName . ' already exists');
            }
        }
        return true;
    }

    /* *****************************************************************************
       Import Objects and Support Functions (in ElementAdapter)
    ***************************************************************************** */

    /* *****************************************************************************
       Export Objects and Support Functions (in ElementAdapter)
    ***************************************************************************** */

    /* *****************************************************************************
       Build Vehicle and Support Functions
    ***************************************************************************** */
    final public function buildVehicle() { //Add to the Transport Package
        /* @var $myComponent MyComponentProject */
        if (parent::buildVehicle()) { //Return Success
            $myComponent->log(modX::LOG_LEVEL_INFO, 'Packaged Resource: ' . $this->properties['pagetitle']);
            return true;
        }
        else {
            return false;
        }
    }
}