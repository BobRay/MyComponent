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
$modx->regClientCSS($[[+packageNameLower]]->config['cssUrl'].'mgr.css');
$modx->regClientStartupScript($[[+packageNameLower]]->config['jsUrl'].'[[+packageNameLower]].js');
$modx->regClientStartupHTMLBlock('
<script type="text/javascript">
    Ext.onReady(function () {
        [[+packageName]].config = '.$modx->toJSON($[[+packageNameLower]]->config).';
        [[+packageName]].config.connector_url = "'.$[[+packageNameLower]]->config['connectorUrl'].'";
    });
</script>');

return '';