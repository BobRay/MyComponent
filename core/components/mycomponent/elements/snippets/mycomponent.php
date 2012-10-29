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
        case 'lexiconhelper':
            $output = $modx->runSnippet('LexiconHelper');
            break;
        case 'build':
            $modx->runSnippet('Build');
            break;

        case 'removeobjects':
            $modx->runSnippet('RemoveObjects');
            break;
    }


}


return $tpl . '<pre>' . $output;