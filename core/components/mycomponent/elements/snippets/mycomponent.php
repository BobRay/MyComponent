<?php
/* @var $modx modX */
$tpl = $modx->getChunk('MyComponentForm');

$output = '';
if (!empty($_POST)) {
    $action = $_POST['doit'];
    switch($action) {
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

return $tpl . $output;