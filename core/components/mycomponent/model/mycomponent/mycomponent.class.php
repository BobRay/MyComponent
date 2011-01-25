<?php

/**
 * MyComponent
 *
 * Copyright 2011 Your Name <you@yourdomain.com>
 *
 * @author Your Name <you@yourdomain.com>
 * 1/15/11
 *
 * MyComponent is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */
/**
 * MODx MyComponent Class
 *
 * @version Version 1.0.0 Beta-1
 *
 * @package  mycomponent
 *
 * Description
 */

/* Example Class File */
class Mycomponent {

   /**
    * @var modx object Reference pointer to modx object
    */
    protected $modx;
    /**
     * @var array scriptProperties array
     */
    protected $props;
    /**
     * @var array Array of all TVs
     */
    protected $allTvs;
    /**
     * @var array Array of error messages
     */
    protected $errors;
    /**
     * @var modResource The current resource
     */
    protected $resource;
    /**
     * @var int ID of the resource's parent
     */
    protected $parentId;
    /**
     * @var modResource The parent object
     */
    protected $parentObj;
    /**
     * @var boolean Indicates that we're editing an existing resource (ID of resource) 
     */
    protected $existing;
    /**
     * @var boolean Indicates a repost to self
     */
    protected $isPostBack;
    /**
     * @var string Path to MyComponent Core
     */
    protected $corePath;
    /**
     * @var string Path to MyComponent assets directory
     */
    protected $assetsPath;
    /**
     * @var string URL to MyComponent Assets directory
     */
    protected $assetsUrl;
    /**
     * @var boolean Use alias as title
     */
    protected $aliasTitle;
    /**
     * @var boolean Clear the cache after saving doc
     */
    protected $clearcache;
    /**
     * @var string Content of optional header chunk
     */
    protected $header;
    /**
     * @var string Content of optional footer chunk
     */
    protected $footer;
    /**
     * @var int Max size of listbox TVs
     */
    protected $listboxMax;
    /**
     * @var int Max size of multi-select listbox TVs
     */
    protected $multipleListboxMax;
    /**
     * @var string prefix for placeholders
     */
    protected $prefix;
    /**
     * @var string Comma-separated list of words to remove
     */
    protected $badwords;
    /**
     * @var string Value for published resource field
     */
    protected $published;
    /**
     * @var string Value for hidemenu resource field
     */
    protected $hideMenu;
    /**
     * @var string Value for alias resource field
     */
    protected $alias;
    /**
     * @var string Value for cacheable resource field
     */
    protected $cacheable;
    /**
     * @var string Value for searchable resource field
     */
    protected $searchable;
    /**
     * @var string Value for template resource field
     */
    protected $template;
    /**
     * @var string Value for richtext resource field
     */
    protected $richtext;
    /**
     * @var array Array of Tpl chunk contents
     */
    protected $tpls;
    /**
     * @var string Comma-separated list or resource groups to
     * assign new docs to
     */
    protected $groups;
    /**
     * @var int Max length for integer input fields
     */
    protected $intMaxlength;
    /**
     * @var int Max length for text input fields
     */
    protected $textMaxlength;


    /** MyComponent constructor
     *
     * @access public
     * @param (reference object) $modx - modx object
     * @param (reference array) $props - scriptProperties array.
     */

    public function __construct(&$modx, &$props) {
        $this->modx =& $modx;
        $this->props =& $props;
        /* NP paths; Set the np. System Settings only for development */
        $this->corePath = $this->modx->getOption('np.core_path', null, MODX_CORE_PATH . 'components/mycomponent/');
        $this->assetsPath = $this->modx->getOption('np.assets_path', null, MODX_ASSETS_PATH . 'components/mycomponent/');
        $this->assetsUrl = $this->modx->getOption('np.assets_url', null, MODX_ASSETS_URL . 'components/mycomponent/');
    }

    /** Sets Postback status
     *
     * @access public
     * @param $setting (bool) desired setting */
    public function setPostBack($setting) {
        $this->isPostBack = $setting;
    }

    /** gets Postback status. Used by snippet to determine
     * postback status.
     *
     * @access public
     * @return (bool) true if set, false if not
     */

    public function getPostBack() {
        return $this->isPostBack;
    }

    /** Initialize variables and placeholders.
     *  Uses $_POST on postback.
     *  Checks for an existing resource to edit in $_POST.
     *  Sets errors on failure.
     *
     *  @access public
     *  @param (string) $context - current context key
     */

        public function init($context) {

            switch ($context) {
                case 'mgr':
                    break;
                case 'web':
                default:
                    $language = !empty($this->props['language'])
                            ? $this->props['language'] . ':' : '';
                    $this->modx->lexicon->load($language . 'mycomponent:default');
                    break;
            }
            $this->prefix =  empty($this->props['prefix']) ? 'np' : $this->props['prefix'];
            /* see if we're editing an existing doc */
            $this->existing = false;
            if (isset($_POST['np_existing']) && $_POST['np_existing'] == 'true') {
                $this->existing = is_numeric($_POST['np_doc_id'])
                        ? $_POST['np_doc_id'] : false;
            }

            /* see if it's a repost */
            $this->setPostback(isset($_POST['hidSubmit']) && $_POST['hidSubmit'] == 'true');

            if($this->existing) {

                $this->resource = $this->modx->getObject('modResource', $this->existing);
                if ($this->resource) {

                    if (!$this->modx->hasPermission('view_document') || !$this->resource->checkPolicy('view') ) {
                        $this->setError($this->modx->lexicon('np_view_permission_denied'));
                    }
                    if ($this->isPostBack) {
                        /* str_replace to prevent rendering of placeholders */
                         $fs = array();
                         foreach($_POST as $k=>$v) {
                             $fs[$k] = str_replace(array('[',']'),array('&#91;','&#93;'),$v);
                         }
                        $this->modx->toPlaceholders($fs,$this->prefix);


                    } else {
                        $ph = $this->resource->toArray();
                        $tags = false;
                        foreach($ph as $k=>$v) {
                            if (strstr($v, '[[')) {
                                $tags = true;
                            }
                            if ($tags && ! $this->modx->hasPermission('allow_modx_tags')) {
                                $this->setError($this->modx->lexicon('np_no_modx_tags'));
                                return;
                            }
                            $fs[$k] = str_replace(array('[',']'),array('&#91;','&#93;'),$v);
                        }
                        $ph = $fs;
                        $this->modx->toPlaceholders($ph,$this->prefix);
                        unset($ph);
                    }
                } else {
                   $this->setError($this->modx->lexicon('np_no_resource') . $this->existing);
                   return;

                }
                /* need to forward this from $_POST so we know it's an existing doc */
                $stuff = '<input type="hidden" name="np_existing" value="true" />' . "\n" .
                '<input type="hidden" name="np_doc_id" value="' . $this->resource->get('id') . '" />';
                $this->modx->toPlaceholder('post_stuff',$stuff,$this->prefix);

            } else {
                /* new document */
                if (!$this->modx->hasPermission('new_document')) {
                    $this->setError($this->modx->lexicon('np_create_permission_denied'));
                }
                $this->resource = $this->modx->newObject('modResource');
                /* get folder id where we should store articles
                 else store under current document */
                 $this->parentId = !empty($this->props['parent']) ? intval($this->props['parent']):$this->modx->resource->get('id');

                /* str_replace to prevent rendering of placeholders */
                 $fs = array();
                 foreach($_POST as $k=>$v) {
                     $fs[$k] = str_replace(array('[',']'),array('&#91;','&#93;'),$v);
                 }
                 $this->modx->toPlaceholders($fs,$this->prefix);


                 $this->aliasTitle = $this->props['aliastitle']? true : false;
                 $this->clearcache = isset($_POST['clearcache'])? $_POST['clearcache'] : $this->props['clearcache'] ? true: false;

                 $this->hideMenu = isset($_POST['hidemenu'])? $_POST['hidemenu'] : $this->_setDefault('hidemenu',$this->parentId);
                 $this->resource->set('hidemenu', $this->hideMenu);

                 $this->cacheable = isset($_POST['cacheable'])? $_POST['cacheable'] : $this->_setDefault('cacheable',$this->parentId);
                 $this->resource->set('cacheable', $this->cacheable);

                 $this->searchable = isset($_POST['searchable'])? $_POST['searchable'] : $this->_setDefault('searchable',$this->parentId);
                 $this->resource->set('searchable', $this->searchable);

                 $this->published = isset($_POST['published'])? $_POST['published'] : $this->_setDefault('published',$this->parentId);
                 $this->resource->set('published', $this->published);

                 $this->richtext = isset($_POST['richtext'])? $_POST['richtext'] : $this->_setDefault('richtext',$this->parentId);
                 $this->resource->set('richtext', $this->richtext);

                 if (! empty($this->props['groups'])) {
                    $this->groups = $this->_setDefault('groups',$this->parentId);
                 }
                 $this->header = !empty($this->props['headertpl']) ? $this->modx->getChunk($this->props['headertpl']) : '';
                 $this->footer = !empty($this->props['footertpl']) ? $this->modx->getChunk($this->props['footertpl']):'';

                 $this->intMaxlength = !empty($this->props['intmaxlength'])? $this->props['intmaxlength'] : 10;
                 $this->textMaxlength = !empty($this->props['textmaxlength'])? $this->props['textmaxlength'] : 60;



            }
             if( !empty($this->props['badwords'])) {
                 $this->badwords = str_replace(' ','', $this->props['badwords']);
                 $this->badwords = "/".str_replace(',','|', $this->badwords)."/i";
             }

           $this->modx->lexicon->load('core:resource');
           $this->template = $this->_getTemplate();
           if($this->props['initdatepicker']) {
                $this->modx->regClientCSS($this->assetsUrl . 'datepicker/css/datepicker.css');
                $this->modx->regClientStartupScript($this->assetsUrl . 'datepicker/js/datepicker.js');
           }

           /* inject NP CSS file */
           /* Empty but sent parameter means use no CSS file at all */

           if (empty($this->props['cssfile'])) { /* nothing sent - use default */
               $css = $this->assetsUrl . 'css/mycomponent.css';
           } elseif (empty($this->props['cssfile']) ) { /* empty param -- no css file */
               $css = false;
           } else {  /* set but not empty -- use it */
               $css = $this->assetsUrl . 'components/mycomponent/css/' . $this->props['cssfile'];
           }

           if ($css !== false) {
               $this->modx->regClientCSS($css);
           }

           $this->listboxMax = $this->props['listboxmax']? $this->props['listboxmax'] : 8;
           $this->MultipleListboxMax = $this->props['multiplelistboxmax']? $this->props['multiplelistboxmax'] : 8;


           $ph = ! empty($this->props['contentrows'])? $this->props['contentrows'] : '10';
           $this->modx->toPlaceholder('contentrows',$ph,$this->prefix);

           $ph = ! empty($this->props['contentcols'])? $this->props['contentcols'] : '60';
           $this->modx->toPlaceholder('contentcols',$ph, $this->prefix);

           $ph = ! empty($this->props['summaryrows'])? $this->props['summaryrows'] : '10';
           $this->modx->toPlaceholder('summaryrows',$ph, $this->prefix);

           $ph = ! empty($this->props['summarycols'])? $this->props['summarycols'] : '60';
           $this->modx->toPlaceholder('summarycols',$ph, $this->prefix);

           /* do rich text stuff */
            //$ph = ! empty($this->props['rtcontent']) ? 'MODX_RichTextWidget':'content';
            $ph = ! empty($this->props['rtcontent']) ? 'modx-richtext':'content';
            $this->modx->toPlaceholder('rt_content_1', $ph, $this->prefix );
            $ph = ! empty($this->props['rtcontent']) ? 'modx-richtext':'content';
            $this->modx->toPlaceholder('rt_content_2', $ph, $this->prefix );

            /* set rich text summary field */

            $ph = ! empty($this->props['rtsummary']) ? 'modx-richtext':'introtext';
            $this->modx->toPlaceholder('rt_summary_1', $ph, $this->prefix );
            $ph = ! empty($this->props['rtsummary']) ? 'modx-richtext':'introtext';
            $this->modx->toPlaceholder('rt_summary_2', $ph, $this->prefix );

            unset($ph);
           if ($this->props['initrte']) {
                /* set rich text content placeholders and includes necessary js files */
               $tinyPath = $this->modx->getOption('core_path').'components/tinymce/';
               $this->modx->regClientStartupScript($this->modx->getOption('manager_url').'assets/ext3/adapter/ext/ext-base.js');
               $this->modx->regClientStartupScript($this->modx->getOption('manager_url').'assets/ext3/ext-all.js');
               $this->modx->regClientStartupScript($this->modx->getOption('manager_url').'assets/modext/core/modx.js');


               $whichEditor = $this->modx->getOption('which_editor',null,'');

               $plugin=$this->modx->getObject('modPlugin',array('name'=>$whichEditor));
               if ($whichEditor == 'TinyMCE' ) {
                   //$tinyUrl = $this->modx->getOption('assets_url').'components/tinymcefe/';
                    $tinyUrl = $this->modx->getOption('assets_url').'components/tinymce/';
                   /* OnRichTextEditorInit */

                   $tinyproperties=$plugin->getProperties();
                   require_once $tinyPath.'tinymce.class.php';
                   $tiny = new TinyMCE($this->modx,$tinyproperties,$tinyUrl);
                   if (isset($this->props['forfrontend']) || $this->modx->isFrontend()) {
                       $def = $this->modx->getOption('cultureKey',null,$this->modx->getOption('manager_language',null,'en'));
                       $tinyproperties['language'] = $this->modx->getOption('fe_editor_lang',array(),$def);
                       $tinyproperties['frontend'] = true;
                       // $tinyproperties['selector'] = 'modx-richtext';
                                           //$tinyproperties['selector'] = 'modx-richtext';//alternative to 'frontend = true' you can use a selector for texareas
                       unset($def);
                   }
                   $tinyproperties['cleanup'] = true; /* prevents "bogus" bug */
                   $tinyproperties['width'] = empty ($this->props['tinywidth'] )? '95%' : $this->props['tinywidth'];
                   $tinyproperties['height'] = empty ($this->props['tinyheight'])? '400px' : $this->props['tinyheight'];

                   $tiny->setProperties($tinyproperties);

                   $html = $tiny->initialize();

                   $this->modx->regClientStartupScript($tiny->config['assetsUrl'].'jscripts/tiny_mce/langs/'.$tiny->properties['language'].'.js');
                   $this->modx->regClientStartupScript($tiny->config['assetsUrl'].'tiny.browser.js');
                   $this->modx->regClientStartupHTMLBlock('<script type="text/javascript">
                       Ext.onReady(function() {
                       MODx.loadRTE();
                       });
                   </script>');
               } /* end if ($whichEditor == 'TinyMCE') */

           } /* end if ($richtext) */

        } /* end init */

    /** Sets default values for published, hidemenu, searchable,
     * cacheable, and groups (if sent).
     *
     * @access protected
     *
     * @param (string) $field - name of resource field
     * @param (int) $parentId - ID of parent resource
     *
     * @return (mixed) returns boolean option, JSON string for
     * groups, and null on failure
     */

    protected function _setDefault($field,$parentId) {

        $retVal = null;
        $prop = $this->props[$field];
        if ($prop == 'Parent' || $prop == 'parent') {
            /* get parent if we don't already have it */
            if (! $this->parentObj) {
                $this->parentObj = $this->modx->getObject('modResource',$this->parentId);
            }
            if (! $this->parentObj) {
                $this->setError('&amp;' .$this->modx->lexicon('np_no_parent'));
                return $retVal;
            }
        }
        $prop = (string) $prop; // convert booleans
        $prop == 'Yes'? '1': $prop;
        $prop = $prop == 'No'? '0' :$prop;

        if ($prop != 'System Default') {
            if ($prop === '1' || $prop === '0') {
                $retVal = $prop;

            } elseif ($prop == 'parent' || $prop === 'Parent') {
                if ($field == 'groups') {
                    $groupString = $this->_setGroups($prop, $this->parentObj);
                    $retVal = $groupString;
                    unset($groupString);
                } else {
                    $retVal = $this->parentObj->get($field);
                }
            } elseif ($field == 'groups') {
                /* ToDo: Sanity Check groups here (or in _setGroups() ) */
                $retVal = $this->_setGroups($prop);
            }
        } else { /* not 1, 0, or parent; use system default except for groups */
            switch($field) {

                case 'published':
                    $option = 'publish_default';
                    break;

                case 'hidemenu':
                    $option = 'hidemenu_default';
                    break;

                case 'cacheable':
                    $option = 'cache_default';
                    break;

                case 'searchable':
                    $option = 'search_default';
                    break;

                case 'richtext':
                    $option = 'richtext_default';
                    break;

                default:
                    $this->setError($this->modx->lexicon('np_unknown_field') . $field);
                    return;
            }
            if ($option != 'groups') {
                $retVal = $this->modx->getOption($option);
            }
            if ($retVal === null) {
                $this->setError($this->modx->lexicon('np_no_system_setting') . $option);
            }

        }
        if ($retVal === null) {
            $this->setError($this->modx->lexicon('np_illegal_value') . $field . ': ' . $prop . $this->modx->lexicon('np_no_permission') );
        }
        return $retVal;
    }

    /** Sets the array of Tpl strings use to create the form.
     *  Attempts to get chunks of names are send as parameters,
     *  used defaults if not.
     *
     *  @access public
     *
     *  @return (bool) true on success, false if a non-empty tpl property
     *  is send and it fails to find the named chunk.
     */

    public function getTpls() {
            $this->tpls = array();

            /* this is the outer Tpl for the whole page */
        $this->tpls['outerTpl'] = !empty ($this->props['outertpl'])? $this->modx->getChunk($this->props['outertpl']): $this->modx->getChunk('npOuterTpl');
        $this->tpls['textTpl'] = ! empty ($this->props['texttpl'])? $this->modx->getChunk($this->props['texttpl']) : $this->modx->getChunk('npTextTpl');
        $this->tpls['intTpl'] = ! empty ($this->props['inttpl'])? $this->modx->getChunk($this->props['inttpl']) : $this->modx->getChunk('npIntTpl');
        $this->tpls['dateTpl'] = ! empty ($this->props['datetpl'])? $this->modx->getChunk($this->props['datetpl']) : $this->modx->getChunk('npDateTpl');
        $this->tpls['boolTpl'] = ! empty ($this->props['booltpl'])? $this->modx->getChunk($this->props['booltpl']) : $this->modx->getChunk('npBoolTpl');
        $this->tpls['textareaTpl'] = ! empty ($this->props['textareatvtpl'])? $this->modx->getChunk($this->props['textareatvtpl']) : $this->modx->getChunk('npTextareaTpl');
        $this->tpls['imageTpl'] = ! empty ($this->props['imagetpl'])? $this->modx->getChunk($this->props['imagetpl']) : $this->modx->getChunk('npImageTpl');
        $this->tpls['optionOuterTpl'] = ! empty ($this->props['optionoutertpl'])? $this->modx->getChunk($this->props['optionoutertpl']) : $this->modx->getChunk('npOptionOuterTpl');
        $this->tpls['listOuterTpl'] = ! empty ($this->props['listoutertpl'])? $this->modx->getChunk($this->props['listoutertpl']) : $this->modx->getChunk('npListOuterTpl');
        $this->tpls['optionTpl'] = ! empty ($this->props['optiontpl'])? $this->modx->getChunk($this->props['optiontpl']) : $this->modx->getChunk('npOptionTpl');
        $this->tpls['listOptionTpl'] = ! empty ($this->props['listoptiontpl'])? $this->modx->getChunk($this->props['listoptiontpl']) : $this->modx->getChunk('npListOptionTpl');
        $this->tpls['errorTpl'] = ! empty ($this->props['errortpl'])? $this->modx->getChunk($this->props['errortpl']) : $this->modx->getChunk('npErrorTpl');
        $this->tpls['fieldErrorTpl'] = ! empty ($this->props['fielderrortpl'])? $this->modx->getChunk($this->props['fielderrortpl']) : $this->modx->getChunk('npFieldErrorTpl');


        /* make sure we have all of them */
        $success = true;
        foreach($this->tpls as $tpl=>$val) {
            if (empty($val)) {
                $this->setError($this->modx->lexicon('np_no_tpl') . $tpl);
                $success = false;
            }
        }
        /* Set these templates for sure so we can show any errors */

        if (empty($this->tpls['outerTpl'])) {
            $this->tpls['outerTpl'] = '<div class="mycomponent">
        <h2>[[%np_main_header]]</h2>
        [[!+np.error_header:ifnotempty=`<h3>[[!+np.error_header]]</h3>`]]
        [[!+np.errors_presubmit:ifnotempty=`[[!+np.errors_presubmit]]`]]
        [[!+np.errors_submit:ifnotempty=`[[!+np.errors_submit]]`]]
        [[!+np.errors:ifnotempty=`[[!+np.errors]]`]]</div>';
        }

        if (empty($this->tpls['errorTpl'])) {
            $this->tpls['errorTpl'] = '<span class = "errormessage">[[+np.error]]</span>';
        }

        if (empty($this->tpls['fieldErrorTpl'])) {
            $this->tpls['fieldErrorTpl'] = '<span class = "fielderrormessage">[[+np.error]]</span>';
        }

        /* set different placeholder prefix if requested */

        if ($this->prefix != 'np') {
            $this->tpls = str_replace('np.', $this->prefix . '.', $this->tpls);
        }

        return $success;
    }
/** return a specified tpl
 *
 * @access public
 * @param (string) tpl name
 *
 * @return (string) tpl content
 *  */

    public function getTpl($name) {
        return $this->tpls[$name];
}
    
    /** Creates the HTML for the displayed form by concatenating
     * the necessary Tpls and calling _displayTv() for any TVs.
     *
     * @access public
     * @param (string) $show - comma-separated list of fields and TVs
     * (name or ID) to include in the form
     *
     * @return (string) returns the finished form
     */
    public function displayForm($show) {

        $fields = explode(',',$show);
        foreach ($fields as $field) {
            $field = trim($field);
        }

        if (! $this->resource) {
            $this->setError($this->modx->lexicon('np_no_resource'));
            return $this->tpls['outerTpl'];
        }

        /* get the resource field names */
        $resourceFieldNames = array_keys($this->modx->getFields('modResource'));

        foreach($fields as $field) {
            $replace = array();
            if (in_array($field,$resourceFieldNames)) { /* regular resource field */

                $replace['[[+npx.help]]'] = $this->props['hoverhelp'] ? '[[%resource_' . $field . '_help:notags]]' : '';
                $replace['[[+npx.caption]]'] = '[[%resource_' . $field . ']]';
                $fieldType = $this->resource->_fieldMeta[$field]['phptype'];
                if ($field == 'hidemenu') {  /* correct schema error */
                    $fieldType = 'boolean';
                }

                /* do content and introtext fields */
                if ($field == 'content') {
                    $replace['[[+npx.rows]]'] = '200';
                    $replace['[[+npx.cols]]'] = '600';

                    if ($this->props['rtcontent']) {
                        if($this->props['initrte']) {
                            $replace['[[+npx.class]]'] = 'modx-richtext';
                        } else {
                            $msg = $this->modx->lexicon('np_no_rte');
                            $this->setError($msg . $field);
                            $this->setFieldError($field, $msg);
                            $replace['[[+npx.class]]'] = 'content';
                        }
                    } else {
                         $replace['[[+npx.class]]'] = 'content';
                    }
                    $inner .= $this->tpls['textareaTpl'];
                } elseif ($field == 'introtext') {
                    $replace['[[+npx.rows]]'] = '200';
                    $replace['[[+npx.cols]]'] = '600';

                    if ($this->props['rtsummary']) {
                        if($this->props['initrte']) {
                            $replace['[[+npx.class]]'] = 'modx-richtext';
                        } else {
                            $msg = $this->modx->lexicon('np_no_rte');
                            $this->setError($msg . $field);
                            $this->setFieldError($field, $msg);
                            $replace['[[+npx.class]]'] = 'introtext';
                        }

                    } else {
                         $replace['[[+npx.class]]'] = 'introtext';
                    }
                    $inner .= $this->tpls['textareaTpl'];
                } else {
                    switch($fieldType) {
                        case 'string':
                            $replace['[[+npx.maxlength]]'] = $this->textMaxlength;
                            $inner .= $this->tpls['textTpl'];
                            break;

                        case 'boolean':
                            $inner .= $this->tpls['boolTpl'];
                            if ($this->isPostBack) {
                                $checked = $_POST[$field];
                            } else {
                                $checked = $this->resource->get($field);
                            }
                            $replace ['[[+npx.checked]]'] = $checked? 'checked="checked"' : '';
                            break;

                        case 'integer':
                            $replace['[[+npx.maxlength]]'] = $this->intMaxlength;
                            $inner .= $this->tpls['intTpl'];
                            break;

                        case 'timestamp':
                            if (! $this->props['initdatepicker']) {
                                $msg = $this->modx->lexicon('np_no_datepicker');
                                $this->setError($msg . $field);
                                $this->setFieldError($field, $msg);
                                //$this->setError($this->modx->lexicon('np_no_datepicker') . $field);
                            }
                            $inner .= $this->tpls['dateTpl'];
                            if (! $this->isPostBack) {
                                $this->_splitDate($field,$this->resource->get($field));
                            }
                            break;
                        default:
                            $replace['[[+npx.maxlength]]'] = $this->textMaxlength;
                            $inner .= $this->tpls['textTpl'];
                            break;
                    }
                }
                /* ToD: add readonly to props */
            $replace['[[+npx.readonly]]'] = ($field =='id') || in_array($field, $this->props['readonly'])? 'readonly="readonly"' : '';
            $replace['[[+npx.fieldName]]'] = $field ;
            $inner = $this->strReplaceAssoc($replace, $inner);

            } else {
                /* see if it's a TV */
                $retVal = $this->_displayTv($field);
                if ($retVal) {
                    $inner .= "\n" . $retVal;
                }
            }
        }

        $formTpl = str_replace('[[+npx.insert]]',$inner,$this->tpls['outerTpl']);
        //die ('<pre' . print_r($formTpl,true));
        return $formTpl;
    } /* end displayForm */



    /** displays an individual TV
     *
     * @access protected
     * @param $tvNameOrId (string) name or ID of TV to process.
     *
     * @return (string) returns the HTML code for the TV.
     */

    protected function _displayTv($tvNameOrId) {


        if (is_numeric($tvNameOrId)) {
           $tvObj = $this->modx->getObject('modTemplateVar',$tvNameOrId);
        } else {
           $tvObj = $this->modx->getObject('modTemplateVar',array('name' => $tvNameOrId));
        }
        if (empty($tvObj)) {
            $this->setError($this->modx->lexicon('np_no_tv') . $tvNameOrId);
            return null;
        } else {
            /* make sure requested TV is attached to this template*/
            $tvId = $tvObj->get('id');
            $found = $this->modx->getCount('modTemplateVarTemplate', array('templateid' => $this->template, 'tmplvarid' => $tvId));
            if (! $found) {
                $this->setError($this->modx->lexicon('np_not_our_tv') . ' Template: ' . $this->template . '  ----    TV: ' . $tvNameOrId);
                return null;
            } else {
                $this->allTvs[] = $tvObj;
            }
        }


    /* we have a TV to show */
    /* Build TV template dynamically based on type */

        $formTpl = '';
        $tv = $tvObj;
        $fields = $tv->toArray();

        /* use TV's name as caption if caption is empty */
        $caption = empty($fields['caption'])? $fields['name'] : $fields['caption'];

        /* Build TV input code dynamically based on type */
        $tvType = $tv->get('type');
        $tvType = $tvType == 'option'? 'radio' : $tvType;

        /* set TV to current value or default if not postBack */
        if (! $this->isPostBack ) {
            $ph = '';
            if ($this->existing) {
                $ph = $tv->getValue($this->existing);
            }
            /* empty value gets default_text for both new and existing docs */
            if (empty($ph)) {
                $ph = $tv->get('default_text');
            }
            if (stristr($ph,'@EVAL') || stristr($_POST[$fields['name']],'@EVAL') || stristr($_POST[$fields['name'].'_time'], '@eval')) {
                $this->setError($this->modx->lexicon('np_no_evals'). $tv->get('name'));
                return null;
            } else {
                $this->modx->toPlaceholder($fields['name'], $ph, $this->prefix );
            }
        }

        $replace = array();
        $replace['[[+npx.help]]'] = $this->props['hoverhelp'] ? $fields['description'] :'';
        $replace['[[+npx.caption]]'] = $caption;

        $replace['[[+npx.fieldName]]'] = $fields['name'];
        switch ($tvType) {
            case 'date':
                if (! $this->props['initdatepicker']) {
                    $msg = $this->modx->lexicon('np_no_datepicker');
                    $this->setError($msg . $fields['name']);
                    $this->setFieldError($fields['name'], $msg);
                    //$this->setError($this->modx->lexicon('np_no_datepicker') . $fields['name']);
                }
                $formTpl .= $this->tpls['dateTpl'];
                if (! $this->isPostBack) {
                    $this->_splitDate($fields['name'], $tv->renderOutput());
                }
                break;

            default:
            case 'text':
            case 'textbox':
            case 'email';
                $formTpl .= $this->tpls['textTpl'];
                $replace['[[+npx.maxlength]]'] = $this->textMaxlength;
                break;
            case 'image';
                /* ToDo: image browser (someday) */
                $replace['[[+npx.help]]'] = $this->props['hoverhelp'] ? $fields['description'] : '';
                $replace['[[+npx.maxlength]]'] = $this->textMaxlength;
                $formTpl .= $this->tpls['imageTpl'];
                break;

            case 'textarea':
            case 'textareamini':
                $replace['[[+npx.cols]]'] = 200;
                $replace['[[+npx.rows]]'] = 600;
                $formTpl .= $this->tpls['textareaTpl'];
                $replace['[[+npx.class]]'] = $tvType == 'textarea' ? 'textarea' : 'textareamini';
                break;

            case 'richtext':
                $replace['[[+npx.rows]]'] = '200';
                $replace['[[+npx.cols]]'] = '600';
                if ($this->props['initrte']) {
                    $replace['[[+npx.class]]'] = 'modx-richtext';
                } else {
                    //$this->setError($this->modx->lexicon('np_no_rte') . $fields['name']);
                    $msg = $this->modx->lexicon('np_no_rte');
                    $this->setError($msg . $fields['name']);
                    $this->setFieldError($fields['name'], $msg);
                    $replace['[[+npx.class]]'] = 'textarea';
                }
                $formTpl .= $this->tpls['textareaTpl'];

                break;

            case 'number':
                $formTpl .= $this->tpls['intTpl'];
                $replace['[[+npx.maxlength]]'] = $this->intMaxlength;
                break;

            /********* Options *********/

            case 'radio':
            case 'checkbox':
            case 'listbox':
            case 'listbox-multiple':
                $innerReplace = array();

                $options = explode('||',$fields['elements']);
                $postfix = ($tvType == 'checkbox' || $tvType=='listbox-multiple')? '[]' : '';
                $innerReplace['[[+npx.name]]'] = $fields['name'] . $postfix;

                if($tvType == 'listbox' || $tvType == 'listbox-multiple') {
                    $formTpl = $this->tpls['listOuterTpl'];
                    $innerReplace['[[+npx.multiple]]'] = ($tvType == 'listbox-multiple')? ' multiple="multiple" ': '';
                    $count = count($options);
                    $max = ($tvType == 'listbox')? $this->listboxMax : $this->multipleListboxMax;
                    $innerReplace['[[+npx.size]]'] = ($count <= $max)? $count : $max;
                } else {
                    $formTpl = $this->tpls['optionOuterTpl'];
                }

                $innerReplace['[[+npx.hidden]]'] = ($tvType == 'checkbox') ? '<input type="hidden" name="' . $fields['name'] . '[]" value="" />' : '';
                $innerReplace['[[+npx.class]]'] = 'np-tv-' . $tvType;
                $innerReplace['[[+npx.help]]'] = $this->props['hoverhelp'] ? $fields['description'] : '';

                /* Do outer TPl replacements */
                $formTpl = $this->strReplaceAssoc($innerReplace,$formTpl);

                /* new replace array for options */
                $innerReplace = array();
                $innerReplace['[[+npx.name]]'] = $fields['name'] . $postfix;

                /* get TVs current value from DB or $_POST */
                if ($this->existing  && ! $this->isPostBack)  {
                    if (is_array($options)) {
                        $val = explode('||',$tv->getValue($this->existing));
                    } else {
                        $val = $tv->renderOutput($this->existing);
                    }
                } else {
                    $val = $_POST[$fields['name']];
                }
                $inner = '';

                /* loop through options and set selections */
                foreach ($options as $option) {

                    /* if field is empty and not in $_POST, get the default value */
                    if(empty($val) && !isset($_POST[$fields['name']])) {
                        $defaults = explode('||',$fields['default_text']);
                        $option = strtok($option,'=');
                        $rvalue = strtok('=');
                        $rvalue = $rvalue? $rvalue : $option;
                    } else {
                        $rvalue = $option;
                    }
                    if ($tvType == 'listbox' || $tvType =='listbox-multiple') {
                        $optionTpl = $this->tpls['listOptionTpl'];
                        $innerReplace['[[+npx.value]]'] = $rvalue;
                    } else {
                        $optionTpl = $this->tpls['optionTpl'];
                        $innerReplace['[[+npx.class]]'] = $tvType;
                        $innerReplace['[[+npx.type]]'] = $tvType;
                        $innerReplace['[[+npx.name]]'] = $fields['name'].$postfix;
                        $innerReplace['[[+npx.value]]'] = $rvalue;
                    }

                    /* Set string to use for selected options */
                    $selected = ($tvType == 'radio' || $tvType == 'checkbox')? 'checked="checked"' : 'selected="selected"';

                    $innerReplace['[[+npx.selected]]'] = ''; /* default to not set */

                    /* empty and not in $_POST -- use default */
                    if (empty($val)  && !isset($_POST[$fields['name']])) {
                        if ($fields['default_text'] == $rvalue || in_array($rvalue,$defaults) ){
                            $innerReplace['[[+npx.selected]]'] = $selected;
                        }

                    /*  field value is not empty */
                    } elseif ((is_array($val) && in_array($option,$val)) || ($option == $val)) {

                                $innerReplace['[[+npx.selected]]'] = $selected;
                    }

                    $innerReplace['[[+npx.text]]'] = $option;
                    $optionTpl = $this->strReplaceAssoc($innerReplace,$optionTpl);
                    $inner .= $optionTpl;

                } /* end of option loop */

                $formTpl = str_replace('[[+npx.options]]',$inner, $formTpl);
                break;

        }  /* end switch */
        $formTpl = $this->strReplaceAssoc($replace, $formTpl);
        return $formTpl;
    }

    /** Uses an associative array for string replacement
     *
     * @param $replace - (array) associative array of keys and values
     * @param &$subject - (string) string to do replacements in
     * @return (string) - modified subject */

    public function strReplaceAssoc(array $replace, $subject) {
       return str_replace(array_keys($replace), array_values($replace), $subject);
    }
    /** Splits time string into date and time and sets
     * placeholders for each of them
     *
     * @access protected
     * @param $ph - (string) placeholder to set
     * @param $timeString - (string) time string
     *  */

    protected function _splitDate($ph,$timeString) {
        $s = substr($timeString,11,5);
        $s = $s? $s : '';
        $this->modx->toPlaceholder($ph . '_time' , $s, $this->prefix);
        $s = substr($timeString,0,10);
        $s = $s? $s : '';
        $this->modx->toPlaceholder($ph, $s, $this->prefix);

    }

    /** Saves the resource to the database.
     *
     * @access public
     * @return - (int) returns the ID of the created or edited resource,
     * or empty string on error.
     * Used by snippet to forward the user.
     *
     */

    public function saveResource() {

        if (!$this->modx->hasPermission('allow_modx_tags')) {
            $allowedTags = '<p><br><a><i><em><b><strong><pre><table><th><td><tr><img><span><div><h1><h2><h3><h4><h5><font><ul><ol><li><dl><dt><dd>';
            foreach ($_POST as $k => $v)
                if (!is_array($v)) { /* leave checkboxes, etc. alone */
                    $_POST[$k] = $this->modx->stripTags($v, $allowedTags);
                }
        }
        $oldFields = $this->resource->toArray();

        if (!empty($this->badwords)) {
            foreach ($_POST as $field => $val) {
                if (!is_array($val)) {
                    $_POST[$field] = preg_replace($this->badwords, '[Filtered]', $val); // remove badwords
                }
            }
        }

        /* correct timestamp resource fields */
        foreach ($_POST as $field => $val) {
            if ($this->resource->_fieldMeta[$field]['phptype'] == 'timestamp') {
                if (empty($_POST[$field])) {
                    unset($_POST[$field]);
                } else {
                    $_POST[$field] = $val . ' ' . $_POST[$field . '_time'];
                }
            }
        }
        $fields = array_merge($oldFields, $_POST);
        if (!$this->existing) { /* new document */

            /* ToDo: Move this to init()? */
            /* set alias name of document used to store articles */
            if (empty($fields['alias'])) { /* leave it alone if filled */
                if (!$this->aliasTitle) {
                    if (!empty($this->props['aliasprefix'])) {
                        $alias = $this->props['aliasprefix'] . '-' . time();
                    } else {
                        $alias = time();
                    }
                } else { /* use pagetitle */
                    $alias = $this->modx->stripTags($_POST['pagetitle']);
                    $alias = strtolower($alias);
                    $alias = preg_replace('/&.+?;/', '', $alias); // kill entities
                    $alias = preg_replace('/[^\.%a-z0-9 _-]/', '', $alias);
                    $alias = preg_replace('/\s+/', '-', $alias);
                    $alias = preg_replace('|-+|', '-', $alias);
                    $alias = trim($alias, '-');

                }
                $fields['alias'] = $alias;
            }
            /* set fields for new object */

            /* set editedon and editedby for existing docs */
            $fields['editedon'] = '0';
            $fields['editedby'] = '0';

            /* these *might* be in the $_POST array. Set them if not */
            $fields['hidemenu'] = isset($_POST['hidemenu'])? $_POST['hidemenu']: $this->hidemenu;
            $fields['template'] = isset ($_POST['template']) ? $_POST['template'] : $this->template;
            $fields['parent'] = isset ($_POST['parent']) ? $_POST['parent'] : $this->parentId;
            $fields['searchable'] = isset ($_POST['searchable']) ? $_POST['searchable'] : $this->searchable;
            $fields['cacheable'] = isset ($_POST['cacheable']) ? $_POST['cacheable'] : $this->cacheable;
            $fields['richtext'] = isset ($_POST['richtext']) ? $_POST['richtext'] : $this->richtext;
            $fields['createdby'] = $this->modx->user->get('id');
            $fields['content']  = $this->header . $fields['content'] . $this->footer;

        }

        /* Add TVs to $fields for processor */
        /* e.g. $fields[tv13] = $_POST['MyTv5'] */
        /* processor handles all types */

        if (!empty($this->allTvs)) {
            $fields['tvs'] = true;
            foreach ($this->allTvs as $tv) {
                $name = $tv->get('name');
                if ($tv->get('type') == 'date') {
                    $fields['tv' . $tv->get('id')] = $_POST[$name] . ' ' . $_POST[$name . '_time'];
                } else {
                    $fields['tv' . $tv->get('id')] = $_POST[$name];
                }
            }
        }
        /* set groups for new doc if param is set */
        if ((!empty($this->groups) && (!$this->existing))) {
            $fields['resource_groups'] = $this->groups;
        }

        /* one last error check before calling processor */
        if (!empty($this->errors)) {
            /* return without altering the DB */
            return '';
        }
        if ($this->props['clearcache']) {
            $fields['syncsite'] = true;
        }
        /* call the appropriate processor to save resource and TVs */
        if ($this->existing) {
            $response = $this->modx->runProcessor('resource/update', $fields);
        } else {
            $response = $this->modx->runProcessor('resource/create', $fields);
        }
        if ($response->isError()) {
            if ($response->hasFieldErrors()) {
                $fieldErrors = $response->getAllErrors();
                $errorMessage = implode("\n", $fieldErrors);
            } else {
                $errorMessage = 'An error occurred: ' . $response->getMessage();
            }
            $this->setError($errorMessage);
            return '';

        } else {
            $object = $response->getObject();

            $postId = $object['id'];

            /* clean post array */
            $_POST = array();
        }

        if (!$postId) {
            $this->setError('np_post_save_no_resource');
        }
        return $postId;

    } /* end saveResource() */

        /** Forward user to another page (default is edited page)
         *
         *  @access public
         *  @param (int) $postId - ID of page to forward to
         *  */

        public function forward($postId) {
            if (empty($postId)) {
                $postId = $this->existing? $this->existing : $this->resource->get('id');
            }
            /* clear cache on new resource */
            if (! $this->existing) {
               $cacheManager = $this->modx->getCacheManager();
               $cacheManager->clearCache(array (
                    "{$this->resource->context_key}/",
                ),
                array(
                    'objects' => array('modResource', 'modContext', 'modTemplateVarResource'),
                    'publishing' => true
                    )
                );
            }

            $_SESSION['np_resource_id'] = $this->resource->get('id');
            $goToUrl = $this->modx->makeUrl($postId);

            /* redirect to post id */

            /* ToDo: The next two lines can probably be removed once makeUrl() and sendRedirect() are updated */
            $controller = $this->modx->getOption('request_controller',null,'index.php');
            $goToUrl = $controller . '?id=' . $postId;

            $this->modx->sendRedirect($goToUrl);
        }

    /** creates a JSON string to send in the resource_groups field
     * for resource/update or resource/create processors.
     *
     * @access protected
     * @param string $resourceGroups - a comma-separated list of
     * resource groups names or IDs (or both mixed) to assign a
     * document to.
     *
     * @return (string) (JSON encoded array)
     */

    protected function _setGroups($resourceGroups, $parentObj = null) {

        $values = array();
        if ($resourceGroups == 'parent') {

            $resourceGroups = (array) $parentObj->getMany('ResourceGroupResources');

            if (!empty($resourceGroups)) { /* parent belongs to at lease one resource group */
                /* build $resourceGroups string from parent's groups */
                $groupNumbers = array();
                foreach ($resourceGroups as $resourceGroup) {
                    $groupNumbers[] = $resourceGroup->get('document_group');
                }
                $resourceGroups = implode(',', $groupNumbers);
            } else { /* parent not in any groups */
                //$this->setError($this->modx->lexicon('np_no_parent_groups'));
                return '';
            }


        } /* end if 'parent' */

        $groups = explode(',', $resourceGroups);

        foreach ($groups as $group) {
            $group = trim($group);
            if (is_numeric($group)) {
                $groupObj = $this->modx->getObject('modResourceGroup', $group);
            } else {
                $groupObj = $this->modx->getObject('modResourceGroup', array('name' => $group));
            }
            if (! $groupObj) {
                $this->setError($this->modx->lexicon('np_no_resource_group') . $group);
                return null;
            }
            $values[] = array(
                'id' => $groupObj->get('id'),
                'name' => $groupObj->get('name'),
                'access' => '1',
                'menu' => '',
            );
        }
        //die('<pre>' . print_r($values,true));
        return $this->modx->toJSON($values);

    }

    /** allows strip slashes on an array
     * not used, but may have to be called if magic_quotes_gpc causes trouble
     * */
    /*protected function _stripslashes_deep($value) {
        $value = is_array($value) ?
                array_map('_stripslashes_deep', $value) :
                stripslashes($value);
        return $value;
    }*/

    /** return any errors set in the class
     * @return (array) array of error strings
     */
    public function getErrors() {
        return $this->errors;
    }

    /** add error to error array
     * @param (string) $msg - error message
     */
    public function setError($msg) {
        $this->errors[] = $msg;
    }

    /** Gets template ID of resource
     * @return (int) returns the template ID
     */
    protected function _getTemplate() {
        if ($this->existing) {
            return $this->resource->get('template');
        }
        $template = $this->modx->getOption('default_template');

        if ($this->props['template'] == 'parent') {
            if (empty($this->parentId)) {
                $this->setError($this->modx->lexicon('np_parent_not_sent'));
            }
            if (empty($this->parentObj)) {
                $this->parentObj = $this->modx->getObject('modResource', $this->parentId);
            }
            if ($this->parentObj) {
                $template = $this->parentObj->get('template');
            } else {
                $this->setError($this->modx->lexicon('np_parent_not_found') . $this->parentId);
            }

        } elseif (!empty($this->props->template)) {


            if (is_numeric($this->props['template'])) { /* user sent a number */
                $t = $this->modx->getObject('modTemplate', $this->props['template']);
                /* make sure it exists */
                if (! $t) {
                    $this->SetError($this->modx->lexicon('np_no_template_id') . $this->props['template']);
                }
            } else { /* user sent a template name */
                $t = $this->modx->getObject('modTemplate', array('templatename' => $this->props['template']));
                if (!$t) {
                    $this->setError($this->modx->lexicon('np_no_template_name') . $this->props['template']);
                }
            }
            $template = $t ? $t->get('id')
                        : $this->modx->getOption('default_template');
                unset($t);
        }

        return $template;
    }

    /** Checks form fields before saving.
     *  Sets an error for the header and another for each
     *  missing required field.
     * */

    public function validate() {
        $errorTpl = $this->tpls['fieldErrorTpl'];
        $success = true;
        $fields = explode(',', $this->props['required']);
        if (!empty($fields)) {

            foreach ($fields as $field) {
                if (empty($_POST[$field])) {
                    $success = false;
                    /* set ph for field error msg */
                    $msg = $this->modx->lexicon('np_error_required');
                    $this->setFieldError($field, $msg);
                    /*$msg = str_replace('[[+name]]', $field, $msg);
                    $msg = str_replace("[[+{$this->prefix}.error]]", $msg, $errorTpl);
                    $ph = 'error_' . $field;
                    $this->modx->toPlaceholder($ph, $msg, $this->prefix);*/

                    /* set error for header */
                    $msg = $this->modx->lexicon('np_missing_field');
                    $msg = str_replace('[[+name]]', $field, $msg);
                    $this->setError($msg);

                }
            }
        }

        $fields = explode(',', $this->props['show']);
        foreach ($fields as $field) {
            $field = trim($field);
        }

        foreach ($fields as $field) {
            if (stristr($_POST[$field], '@EVAL')) {
                $this->setError($this->modx->lexicon('np_no_evals_input'));
                $_POST[$field] = '';
                /* set fields to empty string */
                $this->modx->toPlaceholder($field, '', $this->prefix);
                $success = false;
            }

        }

        return $success;
    }

/** Sets placeholder for field error messages
 * @param (string) $fieldName - name of field
 * @param (string) $msg - lexicon error message string
 *
 */
    /* ToDo: Change [[+name]] to [[+npx.something]]?, or ditch it (or not)*/
    public function setFieldError($fieldName, $msg) {
        $msg = str_replace('[[+name]]', $fieldName, $msg);
        $msg = str_replace("[[+{$this->prefix}.error]]", $msg, $this->tpls['fieldErrorTpl']);
        $ph = 'error_' . $fieldName;
        $this->modx->toPlaceholder($ph, $msg, $this->prefix);
    }


} /* end class */


?>
