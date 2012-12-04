<?php


class ContextSettingAdapter extends ObjectAdapter {
    protected $dbClass = 'modContextSetting';
    protected $dbClassIDKey = 'key';
    protected $dbClassNameKey = 'key';
    protected $dbClassParentKey = 'namespace';
    protected $createProcessor = 'context/setting/create';
    protected $updateProcessor = 'context/setting/update';


    /* Database Columns for the XPDO Object */
    protected $myFields;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {

        $this->modx =& $modx;
        $this->helpers =& $helpers;

        if ($mode == MODE_BOOTSTRAP) {

            if (! isset($fields['fk'])) {
                $fields['fk'] = isset($fields['context_key'])? $fields['context_key'] : 0;
            }
            if (!isset($fields['namespace'])) {
                $fields['namespace'] = $this->helpers->props['packageNameLower'];
            }
            if (!isset ($fields['name'])) {
                $fields['name'] = $fields['key'];
            }
            if (is_array($fields)) {
                $this->myFields =& $fields;
            }
            if (!isset($fields['area'])) {
                $fields['area'] = $this->myFields[$this->dbClassParentKey];
            }
        } elseif ($mode == MODE_EXPORT) {
            unset($fields['editedon']);
        }
        $this->name = $fields['key'];
        ObjectAdapter::$myObjects['contextSettings'][] = $fields;
        parent::__construct($modx, $helpers);
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(MODX::LOG_LEVEL_INFO, "\n" . '    Processing Context Settings');
        $settings = $helpers->modx->getOption('contextSettings', ObjectAdapter::$myObjects, array());
        parent::createTransportFile($helpers, $settings, '', 'modContextSetting', $mode);
    }
}