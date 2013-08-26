<?php


class TemplateVarAdapter extends ElementAdapter
{
    protected $dbClass = 'modTemplateVar';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'name';
    protected $dbClassParentKey = 'category';
    protected $createProcessor = 'element/tv/create';
    protected $updateProcessor = 'element/tv/update';
    

    protected $fields;
    protected $name;

    final public function __construct(&$modx, &$helpers, $fields, $mode=MODE_BOOTSTRAP) {
        /* @var $modx modX */
        /* @var $helpers Helpers */
        $this->name = $fields['name'];
        $this->modx =& $modx;
        $this->helpers =& $helpers;
        /* make sure there's a caption */
        if (! isset($fields['caption']) || empty($fields['caption'])) {
            $fields['caption'] = $fields['name'];
        }
        if ($mode == MODE_BOOTSTRAP) {
            if (is_array($fields)) {
                if (isset($fields['templates'])) {
                    $this->setTvResolver($fields['templates'], $mode);
                    unset($fields['templates']);
                }
            }
        } elseif ($mode == MODE_EXPORT) {
            $this->setTvResolver($fields, $mode);
            unset($fields['id']);
        }
        $this->myFields = $fields;
        parent::__construct($modx, $helpers, $fields, $mode);

    }
    public function setTvResolver($fields, $mode) {
        if ($mode == MODE_BOOTSTRAP) {
            foreach($fields as $templateName => $rank) {
                $resolverFields = array();
                $resolverFields['templateid'] = $templateName;
                $resolverFields['tmplvarid'] = $this->getName();
                $resolverFields['rank'] = isset($rank) && !empty($rank) ? $rank : '0';
                ObjectAdapter::$myObjects['tvResolver'][] = $resolverFields;
            }
        } elseif ($mode == MODE_EXPORT) {
            $me = $this->modx->getObject('modTemplateVar', array('name' => $this->getName()));
            if (!$me) {
                $this->helpers->sendLog(modX::LOG_LEVEL_ERROR, '[TemplateVar Adapter] ' .
                $this->modx->lexicon('mc_self_nf'));
            } else {
                $tvts = $me->getMany('TemplateVarTemplates');
                if (!empty($tvts)) {
                    foreach ($tvts as $tvt) {
                        /* @var $tvt modTemplateVarTemplate */
                        $fields = $tvt->toArray();
                        if ($fields['templateid'] == $this->modx->getOption('default_template')) {
                            $templateName = 'default';
                        } else {
                            $templateObj = $this->modx->getObject('modTemplate',
                                $fields['templateid']);
                            $templateName = $templateObj->get('templatename');
                        }

                        $resolverFields = array(
                            'templateid' => $templateName,
                            'tmplvarid' => $this->getName(),
                            'rank' => $fields['rank'],
                        );
                        ObjectAdapter::$myObjects['tvResolver'][] = $resolverFields;
                    }
                }
            }
        }

    }

    public function addToMODx($overwrite = false) {
        $fields = $this->myFields;
        parent::addToModx($overwrite);

    }

    public static function createResolver($dir, $intersects, $helpers, $mode = MODE_BOOTSTRAP) {
        /* ToDo: Export mode */
        /* Create tv.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $helpers->modx->lexicon('mc_creating_tv_resolver'));
            $tpl = $helpers->getTpl('tvresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR,
                    '[TemplateVar Adapter] ' .
                        $helpers->modx->lexicon('mc_tvresolvertpl_empty'));
                return false;
            }

            $fileName = 'tv.resolver.php';

            if (!file_exists($dir . '/' . $fileName) || $mode == MODE_EXPORT) {
                $intersectArray = $helpers->beautify($intersects);
                $tpl = str_replace("'[[+intersects]]'", $intersectArray, $tpl);

                $helpers->writeFile($dir, $fileName, $tpl);
            }
            else {
                $helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' . $fileName . ' ' .
                    $helpers->modx->lexicon('mc_already_exists'));
            }
        }
        return true;
    }
}