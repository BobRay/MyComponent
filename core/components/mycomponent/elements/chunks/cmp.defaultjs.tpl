/**
* JS file for [[+packageName]] extra
*
* Copyright [[+copyright]] by [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
*/
        
/* These are for LexiconHelper:
   $modx->lexicon->load('[[+packageNameLower]]:default');
   include '[[+packageNameLower]].class.php'
 */
var [[+packageName]] = function (config) {
    config = config || {};
    [[+packageName]].superclass.constructor.call(this, config);
};
Ext.extend([[+packageName]], Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('[[+packageNameLower]]', [[+packageName]]);

var [[+packageName]] = new [[+packageName]]();