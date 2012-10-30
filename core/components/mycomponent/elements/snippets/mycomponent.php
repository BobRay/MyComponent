<?php
/* @var $modx modX */

if (!defined('MODX_CORE_PATH')) {
    die('This file can not be run outside of MODX. ');
}

$tpl = $modx->getChunk('mycomponentform.tpl');

$output = '';
if (!empty($_POST)) {
    $action = $_POST['doit'];
    switch ($action) {
        case 'bootstrap':
            $output = $modx->runSnippet('Bootstrap');
            break;
        case 'exportobjects':
            $output = $modx->runSnippet('ExportObjects');
            break;
        case 'importobjects':
            $output = $modx->runSnippet('ImportObjects');
            break;
        case 'lexiconhelper':
            $output = $modx->runSnippet('LexiconHelper');
            break;
        case 'build':
            $output = $modx->runSnippet('Build');
            break;

        case 'removeobjects':
            $output = $modx->runSnippet('RemoveObjects');
            break;
        case 'removeobjectsandfiles':
            $output = $modx->runSnippet('RemoveObjects', array('removeFiles' => true));
            break;
    }


}


return $tpl . '<pre>' . $output;