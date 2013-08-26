<?php
// Include the Base Class (only once)

class SystemEventAdapter extends ObjectAdapter
{//This will never change.
    protected $dbClass = 'modEvent';
    protected $dbClassIDKey = 'name';
    protected $dbClassNameKey = 'name';
    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;


// Database Columns for the XPDO Object
    protected $myFields;
    protected $name;

    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        parent::__construct($modx, $helpers);
        $this->name = $fields['name'];

        if ($mode == MODE_BOOTSTRAP) {
            if (empty($fields['groupname'])) {
                $fields['groupname'] = $this->helpers->props['packageName'];
            }
            if (empty($fields['service'])) {
                $fields['service'] = 1;
            }
        }
        ObjectAdapter::$myObjects['newSystemEvents'][] = $fields;
        $this->myFields = $fields;
    }

    /* Move to ObjectAdapter as alternate method? */
    public function addToMODx($overwrite = false) {
        $name = $this->getName();
        $retVal = false;
        $obj = $this->modx->getObject('modEvent', array('name'=> $name));
        if (! $obj) {
            $event = $this->modx->newObject('modEvent');
            if ($event && $event instanceof modEvent) {
                $event->fromArray($this->myFields, "", true, true);
                if ($event->save()) {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                        $this->modx->lexicon('mc_created_se')
                        . ': ' . $name);
                    $retVal = true;
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '    [SystemEvent Adapter] ' .
                        $this->modx->lexicon('mc_could_not_save_se')
                        . ': ' . $name);
                }
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '    [SystemEvent Adapter] ' .
                    $this->modx->lexicon('mc_could_not_create_se')
                    . ': ' . $name);
            }
        } elseif ($overwrite) {
            foreach($this->myFields as $field => $value) {
                $obj->set($field, $value);
            }
            if ($obj->save()) {
                $retVal = true;
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_updated_se')
                    . ': '. $name);
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_failed_to_update_se')
                    . ': ' . $name);
            }

        } else {
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                $this->modx->lexicon('mc_se_already_exists')
                    . ': '. $name);
            $retVal = -1;
        }
        return $retVal;
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_system_events'));
        $settings = $helpers->modx->getOption('newSystemEvents',ObjectAdapter::$myObjects, array());
        parent::createTransportFile($helpers, $settings, '', 'modEvent', $mode);
    }
}