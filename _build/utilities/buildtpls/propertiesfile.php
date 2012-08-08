<?php
/**
 * Properties file for [[+element]] [[+elementType]]
 *
 * Copyright [[+copyright]] by [[+author]] [[+email]]
 * Created on [[+createdon]]
 *
 * @package [[+packageNameLower]]
 * @subpackage build
 */

function stripPhpTags($filename) {
    $o = file_get_contents($filename);
    $o = str_replace('<?php', '', $o);
    $o = str_replace('?>', '', $o);
    $o = trim($o);
    return $o;
}
/* @var $modx modX */
/* @var $sources array */
