<?php
/**
* Connector file for [[+packageName]] extra
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
*
* @package [[+packageNameLower]]
*/
/* @var $modx modX */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$[[+packageNameLower]]CorePath = $modx->getOption('[[+packageNameLower]].core_path', null, $modx->getOption('core_path') . 'components/[[+packageNameLower]]/');
require_once $[[+packageNameLower]]CorePath . 'model/[[+packageNameLower]]/[[+packageNameLower]].class.php';
$modx->[[+packageNameLower]] = new [[+packageName]]($modx);

$modx->lexicon->load('[[+packageNameLower]]:default');

/* handle request */
$path = $modx->getOption('processorsPath', $modx->[[+packageNameLower]]->config, $[[+packageNameLower]]CorePath . 'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));