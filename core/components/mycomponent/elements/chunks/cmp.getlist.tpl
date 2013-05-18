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

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start',$scriptProperties,0);
$limit = $modx->getOption('limit',$scriptProperties,10);
$sort = $modx->getOption('sort',$scriptProperties,'[[+name]]');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');

$c = $modx->newQuery('mod[[+Element]]');
$c->leftJoin('modCategory','Category');
if (!empty($scriptProperties['search'])) {
    $c->where(array(
        '[[+name]]:LIKE' => '%'.$scriptProperties['search'].'%',
        'OR:description:LIKE' => '%'.$scriptProperties['search'].'%',
    ));
}
$count = $modx->getCount('mod[[+Element]]',$c);
$c->select(array(
    'mod[[+Element]].id',
    'mod[[+Element]].[[+name]]',
    'mod[[+Element]].description',
));
$c->select(array(
    'category_name' => 'Category.category',
));
$c->sortby($sort,$dir);
if ($isLimit) {
    $c->limit($limit,$start);
}
$[[+element]]s = $modx->getCollection('mod[[+Element]]',$c);
//echo $c->toSql();

$list = array();
/* @var $[[+element]] mod[[+Element]] */
foreach ($[[+element]]s as $[[+element]]) {
    $[[+element]]Array = $[[+element]]->toArray();
    $[[+element]]Array['category'] = $[[+element]]->get('category_name');
    $list[]= $[[+element]]Array;
}
return $this->outputArray($list,$count);