<?php
/**
 * Processor file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage processors
 */

/* @var $modx modX */

// comment out the next line to make processor functional
return $modx->error->success();

if (!$modx->hasPermission('save_[[+element]]')) {
    return $modx->error->failure($modx->lexicon('access_denied'));
}        

if (empty($scriptProperties['[[+element]]s'])) {
    return $modx->error->failure($modx->lexicon('orphans.[[+element]]s_err_ns'));
}
/* get parent */
if (!empty($scriptProperties['category'])) {
    $category = $modx->getObject('modCategory',$scriptProperties['category']);
    if (empty($category)) return $modx->error->failure($modx->lexicon('orphans.category_err_nf',
        array('id' => $scriptProperties['category'])));
}

/* iterate over [[+element]]s */
$[[+element]]Ids = explode(',',$scriptProperties['[[+element]]s']);
foreach ($[[+element]]Ids as $[[+element]]Id) {
    $[[+element]] = $modx->getObject('mod[[+Element]]',$[[+element]]Id);
    if ($[[+element]] == null) continue;

    $[[+element]]->set('category',$scriptProperties['category']);
    $[[+element]]->save(3600);
}

return $modx->error->success();                