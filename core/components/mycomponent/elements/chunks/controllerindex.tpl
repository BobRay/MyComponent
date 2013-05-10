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

require_once dirname(dirname(__FILE__)).'/model/[[+packageNameLower]]/[[+packageNameLower]].class.php';
$[[+packageNameLower]] = new [[+packageName]]($modx);
return $[[+packageNameLower]]->initialize('mgr');