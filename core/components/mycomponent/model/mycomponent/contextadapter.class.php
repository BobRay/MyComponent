<?php

class ContextAdapter extends ObjectAdapter {
    protected $dbClass = 'modContext';
    protected $dbClassIDKey = 'key';
    protected $dbClassNameKey = 'key'; /* pagetitle, templatename, name, etc. */
    protected $createProcessor = 'context/create';
    protected $updateProcessor = 'context/update';
    protected string $modx3CreateProcessor =
        'MODX\Revolution\Processors\Context\Create';
    protected string $modx3UpdateProcessor =
        'MODX\Revolution\Processors\Context\Update';


    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        $this->name = $fields['key'];
        parent::__construct($modx, $helpers);
        if (is_array($fields)) {
            $this->myFields = $fields;
        }
        ObjectAdapter::$myObjects['contexts'][] = $fields;
    }

    public static function createTransportFiles(&$helpers, $mode = MODE_BOOTSTRAP) {
        /* @var $helpers Helpers */
        $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
            $helpers->modx->lexicon('mc_processing_contexts'));
        $contexts = $helpers->modx->getOption('contexts', ObjectAdapter::$myObjects, array());
        parent::createTransportFile($helpers, $contexts, '', 'modContext', $mode);
    }

}
