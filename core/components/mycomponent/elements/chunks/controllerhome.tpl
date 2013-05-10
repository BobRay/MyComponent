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

$modx->regClientStartupScript($[[+packageNameLower]]->config['jsUrl'].'widgets/home.panel.js');
$modx->regClientStartupScript($[[+packageNameLower]]->config['jsUrl'].'sections/home.js');
$output = '
<div id="[[+packageNameLower]]-panel-home-div"></div>';

return $output;