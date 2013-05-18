<?php
/**
 * Controller file for [[+packageName]] extra
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
[[+license]]
 *
 * @package [[+packageNameLower]]
 * @subpackage controllers
 */
/* @var $modx modX */

$modx->regClientStartupScript($[[+packageNameLower]]->config['jsUrl'].'widgets/home.panel.js');
$modx->regClientStartupScript($[[+packageNameLower]]->config['jsUrl'].'sections/home.js');
$modx->regClientStartupScript($example->config['jsUrl'] . 'widgets/chunk.grid.js');
$modx->regClientStartupScript($example->config['jsUrl'] . 'widgets/snippet.grid.js');

$output = '<div id="[[+packageNameLower]]-panel-home-div"></div>';

return $output;