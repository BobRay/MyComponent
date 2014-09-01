<?php


class CategoryAdapter extends ObjectAdapter {
    /* These will never change. */
    protected $dbClass = 'modCategory';
    protected $dbClassIDKey = 'id';
    protected $dbClassNameKey = 'category';
    protected $dbClassParentKey = 'parent';
    /* ************** */

    protected $myFields;
    protected $name;
    protected $createProcessor = 'element/category/create';
    protected $updateProcessor = 'element/category/update';


    /* @var $modx modX */
    public $modx;
    /* @var $helpers Helpers */
    public $helpers;


  
    final public function __construct(&$modx, &$helpers, $fields, $mode = MODE_BOOTSTRAP) {
        /* @var $modx modX */
        $this->modx =& $modx;
        $this->helpers =& $helpers;
        $this->name = $fields['category'];
        if ($mode == MODE_BOOTSTRAP) {
            if (! isset($fields['parent']) || empty($fields['parent'])) {
                $fields['parent'] = '0';
            }
        }
        $this->myFields = $fields;
        ObjectAdapter::$myObjects['categories'][$fields['category']] = $fields;

        parent::__construct($modx, $helpers);

    }


/* *****************************************************************************
   Bootstrap and Support Functions (in MODxObjectAdapter)
***************************************************************************** */

    public function addToMODx($overwrite = false) {
        /* create category if necessary */
        $fields = $this->myFields;
        if (isset($fields['parent']) && !empty($fields['parent'])) {
            $pn = $fields['parent'];
            if (!is_numeric($fields['parent'])) {
                $p = $this->modx->getObject('modCategory', array('category' => $pn));
                if ($p) {
                    $fields['parent'] = $p->get('id');
                }
            }
        } else {
            $fields['parent'] = '0';
        }
        $this->myFields = $fields;
        parent::addToModx($overwrite);
    }

    public static function createResolver($dir, $intersects, $helpers, $mode = MODE_BOOTSTRAP) {

        /* Create category.resolver.php resolver */
        /* @var $helpers Helpers */
        if (!empty($dir) && !empty($intersects)) {
            $helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" .
                $helpers->modx->lexicon('mc_creating_category_resolver'));
            $tpl = $helpers->getTpl('categoryresolver.php');
            $tpl = $helpers->replaceTags($tpl);
            if (empty($tpl)) {
                $helpers->sendLog(modX::LOG_LEVEL_ERROR, '[Category Adapter] ' .
                    $helpers->modx->lexicon('mc_categoryresolver_tpl_empty'));
                return false;
            }

            $fileName = 'category.resolver.php';

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

    /* Updates categories.php in target config dir */
    public static function writeCategoryFile($dir, $helpers) {
        /* @var $helpers Helpers */
        $categories = ObjectAdapter::$myObjects['categories'];
        $cats = array();
        if(file_exists($dir . '/categories.php')) {
            $cats = include $dir . '/categories.php';
        }
        $fileArray = array();
        foreach($categories as $elementCategory => $fields) {
            $fileArray[] = isset($fields['category'])? $fields['category'] : $elementCategory;
        }
        /* only update if something has changed */
        if ($fileArray != $cats)  {
            $a = var_export($fileArray, true);
            $content = '<' . '?' . "php\n" . "\$cats = " . $a  . ";\nreturn \$cats;\n";
            $helpers->writeFile($dir, 'categories.php', $content, false);
        }
  }
/* *****************************************************************************
   Export Objects and Support Functions (in MODxObjectAdapter)
***************************************************************************** */

    public function exportElements($toProcess, $dryRun = false) {
        $c = $this->modx->getObject('modCategory', array('category' => $this->myFields['category']));
        if ($c) {
            $this->myId = $c->get('id');
        }
        unset($c);

        foreach($toProcess as $elementType) {
            /* @var $element modElement */

            $class = 'mod' . ucfirst(substr($elementType, 0, -1));
            $adapterName = ucFirst(substr($class, 3)) . 'Adapter';
            $elements = $this->modx->getCollection($class, array('category' => $this->myId));
            if (!empty($elements)) {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                    "\n" .
                        $this->modx->lexicon('mc_processing')
                        . ' ' .  $elementType . ' ' .
                        $this->modx->lexicon('mc_in_category')
                        . ': ' . $this->getName());
            } else {
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, "\n" . '    ' .
                    $this->modx->lexicon('mc_no')
                    . ' ' . $elementType . ' ' .
                    $this->modx->lexicon('mc_found_in_category')
                    . ': ' . $this->getName());
            }
            foreach($elements as $element) {

                if ($class !== 'modPropertySet' && $class !== 'modTemplateVar') {
                    $content = $element->getContent();
                    $element->setContent('');
                    $fields['content'] = $content;
                } else {
                    /* This should never get written anywhere */
                    $content = 'Serious error in CategoryAdapter exportElements()';
                }
                $fields = $element->toArray();
                /* @var $o ElementAdapter */
                $name = $class == 'modTemplate' ? $fields['templatename'] : $fields['name'];
                $elementList = $this->modx->getOption($elementType, $this->helpers->props['elements']);
                foreach ($elementList as $elementName => $propFields) {
                    if ($elementName == $name && isset($propFields['filename'])) {
                        $fields['filename'] = $propFields['filename'];
                    }
                }
                /* These are here for LexiconHelper */
                // include 'chunkadapter.class.php';
                // include 'propertyset.adapter.class.php';
                // include 'snippetadapter.class.php';
                // include 'template.adapter.class.php';
                // include 'templatevar.adapter.class.php'

                $o = new $adapterName($this->modx, $this->helpers, $fields, MODE_EXPORT);
                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                    $this->modx->lexicon('mc_processing')
                . ' ' . $o->getName());

                if (isset($fields['properties']) && !empty($fields['properties'])) {
                    $o->writePropertiesFile($o->getName(), $fields['properties'], MODE_EXPORT);
                }
                if ($class !== 'modPropertySet' && $class !== 'modTemplateVar') {
                    if (!isset($fields['static']) || empty($fields['static'])) {
        /* Overwrite unless static - leave static element files alone */
                        if ($class != 'modSnippet' && $class!='modPlugin') {
                            $o->createCodeFile(true, $content, MODE_EXPORT, $dryRun);
                        } else {
        /* skip single-line plugins and snippets with 'return' */
                            if ( (substr_count($content, ';') == 1) && (stristr($content, 'include'))) {
                                $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                                    $this->modx->lexicon('mc_skipping_include_element_code_file')
                                    . ': ' . $o->getName());
                            } else {
                                $o->createCodeFile(true,
                                    $content, MODE_EXPORT, $dryRun);
                            }
                        }
                    } else {
                        $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '    ' .
                            $this->modx->lexicon('mc_skipping_static_element_code_file')
                        . ': ' . $o->getName());
                    }
                } else {
                    $this->helpers->sendLog(modX::LOG_LEVEL_INFO,
                        '        ' . $this->modx->lexicon('mc_no_code_file_required'));
                }
            }
        }
    }

    public function remove() {
        $name = $this->getName();
        $obj = $this->modx->getObject('modCategory', array('category' => $name));
        if ($obj) {
            $temp = $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $this->helpers->sendLog(modX::LOG_LEVEL_INFO, '        ' .
                $this->modx->lexicon('mc_removed')
                . ' ' . 'modCategory' . ': ' . $name);
            $this->modx->setLogLevel($temp);
            $obj->remove();
        }
    }

}