<?php
// Include the Base Class (only once)

/* Runs once for each new System Setting */
class SystemSettingAdapter extends ObjectAdapter
{//These will never change.
    protected $dbClass = 'modSystemSetting';
    protected $dbClassIDKey = 'key';
    protected $dbClassNameKey = 'key';
    protected $dbClassParentKey = 'namespace';
    protected $createProcessor = 'system/settings/create';
    protected $updateProcessor = 'system/settings/update';
    protected string $modx3CreateProcessor =
        'MODX\Revolution\Processors\System\Settings\Create';
    protected string $modx3UpdateProcessor =
        'MODX\Revolution\Processors\System\Settings\Update';

    /* Database Columns for the XPDO Object */
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* Fields are already set from config */
        $this->modx =& $modx;
        $this->helpers =& $helpers;
        $this->myComponent =& $myComponent;

        if (!isset($fields['namespace'])) {
            $fields['namespace'] = $this->helpers->props['packageNameLower'];
        }
        /* used later */
        $namespace = $fields['namespace'];

        if ($mode == MODE_BOOTSTRAP) {

            /* Get name and description from config file */
           /* $key = $fields['key'];
            if (isset($this->helpers->props['newSystemSettings'])) {
                $settings = $this->helpers->props['newSystemSettings'];
                if (isset($settings[$key]['name'])) {
                    $fields['name'] = $settings[$key]['name'];
                }
                if (isset($settings[$key]['description'])) {
                    $fields['description'] = $settings[$key]['description'];
                }
                if (isset($settings[$key]['xtype'])) {
                    $fields['xtype'] = $settings[$key]['xtype'];
                }
                if (isset($settings[$key]['area'])) {
                    $fields['area'] = $settings[$key]['area'];
                } else {
                    $fields['area'] = '';
                }
            }*/
            $fields['editedon'] = '0000-00-00 00:00:00';
            if (is_array($fields) && !empty($fields)) {
                $this->myFields =& $fields;
            }
        } elseif ($mode == MODE_EXPORT) {
            if (false) {
                $lexField = 'setting_' . $fields['key'];
                $dbObject = $modx->getObject($this->classPrefix . 'modLexiconEntry', array('namespace' => $namespace, 'name' => $lexField));
                $dbString = $dbObject ? $dbObject->get('value') : '';
                // handle ~~, if any
                if (strpos($dbString, '~~') !== false) {
                    $dbString = explode('~~', $dbString);
                    $dbString = $dbString[1];
                }
                $configString = $fields['name'];
                if (strpos($configString, '~~') !== false) {
                    $configString = explode('~~', $configString);
                    $configString = $configString[1];
                }

                // Use $dbString if not empty
                if (!empty($dbString)) {
                    $name = $dbString;
                } elseif (!empty($configString)) {
                    $name = $configString;
                } else { // neither is set
                    $name = '';
                }

                $fields['name'] = $name;

                // Do the same with description
                $lexField = 'setting_' . $fields['key'] . '_desc';
                $dbObject = $modx->getObject($this->classPrefix . 'modLexiconEntry', array('namespace' => $fields['namespace'], 'name' => $lexField));
                $dbString = $dbObject ? $dbObject->get('value') : '';
                // handle ~~, if any
                if (strpos($dbString, '~~') !== false) {
                    $dbString = explode('~~', $dbString);
                    $dbString = $dbString[1];
                }
                $configString = $fields['description'];
                if (strpos($configString, '~~') !== false) {
                    $configString = explode('~~', $configString);
                    $configString = $configString[1];
                }

                // Use $dbString if not empty
                if (!empty($dbString)) {
                    $name = $dbString;
                } elseif (!empty($configString)) {
                    $name = $configString;
                } else { // neither is set
                    $name = '';
                }
                $fields['description'] = $name;
            }
        }
        $this->name = $fields['key'];
        ObjectAdapter::$myObjects['newSystemSettings'][] = $fields;
        parent::__construct($modx, $helpers);
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_system_settings'));
        $settings = $helpers->modx->getOption('newSystemSettings',ObjectAdapter::$myObjects, array());
        parent::createTransportFile($helpers, $settings, '', 'modSystemSetting', $mode);
    }
}
