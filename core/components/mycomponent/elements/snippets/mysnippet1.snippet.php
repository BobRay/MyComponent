<?php
/**
 * MyComponent
 * Copyright 2011 YourName <you@yourdomain.com>
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
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @author Raymond Irving
 * @author YourName <you@yourdomain.com> <http://bobsguides.com>
 
 *
 * Description: The MyComponent snippet presents a form in the front end for
 * creating resources. Rich text editing is available for text fields and TVs.
 *
 * Can be used to edit existing documents in conjunction with the
 * NpEditThisButton snippet.
 * /

/*
  @version Version 1.0.4 Beta-1
  Modified: January 16, 2010

   NOTE: You may need the latest version of TinyMCE for rich text editing.

  Properties:
    @property parent      - (optional) Folder id where new documents are stored; defaults to MyComponent folder.
    @property show        - (optional) Comma separated list of fields/tvs to show (shown in order).
                     defaults to 'pagetitle,longtitle,description,menutitle,pub_date,unpub_date,introtext,content'.
    @property required    - (optional) Comma-separated list of fields/tvs to require; defaults to 'pagetitle,content'.
    @property published   - (optional) Set new resource as published or not
                      (will be overridden by publish and unpublish dates).
                       Set to `parent` to match parent's pub status;
                       defaults to publish_default system setting.
    @property postid      - (optional) Document id to load on success; defaults to the page created or edited.
    @property cancelid    - (optional) Document id to load on cancel; defaults to http_referer.
    @property badwords    - (optional) Comma delimited list of words not allowed in new document.
    @property template    - (optional) Name of template to use for new document; set to 'parent' to use parent's template;
                       for 'parent', &parent must be set; defaults to system default template.
    @property headertpl   - (optional) Header Tpl chunk (chunk name) to be inserted at the beginning of a new document.
    @property footertpl   - (optional) Footer Tpl chunk (chunk name) to be inserted at the end of a new document.
    @property tinyheight  - (optional) Height of richtext areas; default `400px`.
    @property tinywidth   - (optional) Width of richtext areas; default `95%`.
    @property outertpl    - (optional) Tpl used as a shell for the whole page
    @property texttpl     - (optional) Tpl used for text resource fields
    @property inttpl      - (optional) Tpl used for integer resource fields.
    @property datetpl     - (optional) Tpl used for date resource fields and date TVs
    @property booltpl     - (optional) Tpl used for Yes/No resource fields (e.g., published, searchable, etc.).
    @property optionoutertpl - (optional) Tpl used for as a shell for checkbox, list, and radio option TVs.
    @property optiontpl   - (optional) Tpl used for each option of checkbox and radio option TVs.
    @property listoptiontpl - (optional) Tpl used for each option of listbox TVs.
    @property richtext    - (optional) Sets the flag to as to whether or Rich Text Editor is used when editing the page
                       content in the Manager; defaults to richtext_default System Setting for new resources;
                       set to `Parent` to use parent's setting.
    @property rtcontent   - (optional) Use rich text for the content form field.
    @property rtsummary   - (optional) Use rich text for the summary (introtext) form field.
    @property hidemenu    - (optional) Sets the flag (0/1) for whether or not the new page shows in the menu; defaults to 1.
    @property searchable  - (optional) Search add-on components can use this to determine whether to include the resource in searches;
                       default is search_default System Setting; set to `Parent` to use parent's setting.
    @property cacheable   - (optional) Sets the flag (0/1) for whether or not the new page is marked as cacheable;
                       default is cache_default System Setting; set to `Parent` to use parent's setting.

    @property aliastitle  - (optional) Set to 1 to use lowercase, hyphenated, page title as alias. Defaults to 1.
                       If 0,'article-(date created)' is used. Ignored if alias is filled in form.
    @property clearcache  - (optional) When set to 1, cache will be cleared after saving the resource; default: 1.
    @property listboxmax  - (optional) Maximum length for listboxes. Default is 8 items.
    @property cssfile     - (optional) Name of CSS file to use, or `` for no CSS file; defaults to mycomponent.css.
                       File should be in assets/mycomponent/css/ directory
    @property errortpl    - (optional) Name of Tpl chunk for formatting errors in the header. Must contain [[+np.error]] placeholder.
    @property fielderrortpl (optional) Name of Tpl chunk for formatting field errors. Must contain [[+np.error]] placeholder.
    @property groups      - (optional) Resource groups to put new document in (no effect with existing docs);
                       set to 'parent' to use parent's groups.
    @property language    - (optional) Language to use in forms and error messages.
    @property prefix      - (optional) Prefix to use for placeholders; defaults to 'np'
    @property fielderrortpl - (optional)
    @property initrte     - '(optional) Initialize rich text editor; set this if there are any rich text fields; defaults to 0'
    @property initdatepicker - (optional) Initialized the datepicker; set this if there are any date fields; defaults to '1'
    @property readonly    - (optional) Comma-separated list of fields that should be read only; does not work on option or richtext fields
    @property intmaxlength- (optional) Max length for integer input fields; default: 10
    @property textmaxlength- (optional) Max length for text input fields; default 60
    @property hoverhelp    - (optional) Show help when hovering over field caption: default `1`

*/

/** @define "$modx->getOption('np.core_path',null,$modx->getOption('core_path').'components/mycomponent/')" "VALUE" */
require_once $modx->getOption('np.core_path', null, $modx->getOption('core_path') . 'components/mycomponent/') . 'classes/mycomponent.class.php';

/* make sure some prefix is set in $scriptProperties */

$scriptProperties['prefix'] = empty($scriptProperties['prefix'])
        ? 'np' : $scriptProperties['prefix'];
$np_prefix = $scriptProperties['prefix'];

/* create and initialize mycomponent object */
$np = new Mycomponent($modx, $scriptProperties);
$np->init($modx->context->get('key'));
$np->getTpls();


/* get error Tpl chunk */
//$errorTpl = str_replace('[[+prefix]]', $np_prefix, $np->getTpl('errorTpl'));
//$fieldErrorTpl = str_replace('[[+prefix]]', $np_prefix, $np->getTpl('fieldErrorTpl'));

$errorTpl =  $np->getTpl('errorTpl');
$fieldErrorTpl = $np->getTpl('fieldErrorTpl');

/* add Cancel button only if requested */
if (!empty ($cancelId)) {
    $cancelUrl = $modx->makeUrl($cancelId, '', '', 'full');
} else {
    $cancelUrl = isset($_SERVER['HTTP_REFERER'])
            ? $_SERVER['HTTP_REFERER'] : $modx->resource->get('id');
}
$modx->toPlaceholder('cancel_url', $cancelUrl, $np_prefix);

$errorHeaderPresubmit = $modx->lexicon('np_error_presubmit');
$errorHeaderSubmit = $modx->lexicon('np_error_submit');

$formTpl .= $np->displayForm($scriptProperties['show']);

/* just in case */
//$formTpl = str_replace('[[+prefix]]', $np_prefix, $formTpl);

/* handle pre-submission errors */
$errors = $np->getErrors();

if (!empty($errors)) {

    $modx->toPlaceholder('error_header', $errorHeaderPresubmit, $np_prefix);
    foreach ($errors as $error) {
        $errorMessage .= str_replace('[[+' . $np_prefix . '.error]]', $error, $errorTpl);
    }
    $modx->toPlaceholder('errors_presubmit', $errorMessage, $np_prefix);
    return ($formTpl);
}
// get postback status
$isPostBack = $np->getPostBack();

if ($isPostBack) {
    /* check for errors, validate, and save if no errors */
    $errors = $np->getErrors();
    if (!empty($errors)) {
        $modx->toPlaceholder('error_header', $errorHeaderSubmit, $np_prefix);
        foreach ($errors as $error) {
            $errorMessage .= str_replace("[[+{$np_prefix} . '.error]]", $error, $errorTpl);
        }
        $modx->toPlaceholder('errors_submit', $errorMessage, $np_prefix);
        return ($formTpl);

    }

    /* handle pre-save errors (field errors set in validate() ) */
    $np->validate();
    $errors = $np->getErrors();
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $errorMessage .= str_replace("[[+{$np_prefix}.error]]", $error, $errorTpl);
        }
        $modx->toPlaceholder('errors_submit', $errorMessage, $np_prefix);
        $modx->toPlaceholder('error_header', $errorHeaderSubmit, $np_prefix);
        return $formTpl;
    }

    $docId = $np->saveResource(); /* returns ID of edited doc */

    /* if user has set postid, use it, otherwise use ID of the doc */
    $postId = empty($scriptProperties['postid']) ? $docId : $scriptProperties['postid']  ;
    

    /* handle save errors */
    $errors = $np->getErrors();

    if (!empty($errors)) {
        $modx->toPlaceholder('error_header', $errorHeaderSubmit, $np_prefix);
        foreach ($errors as $error) {
            $errorMessage .= str_replace("[[+{$np_prefix}.error]]", $error, $errorTpl);
        }

        $modx->toPlaceholder('errors_submit', $errorMessage, $np_prefix);

        return ($formTpl);
    } else { /* successful save -- forward user */
        $np->forward($postId);
    }
} else { /* just return the form */
    return $formTpl;
}
?>
