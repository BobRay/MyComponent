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

Ext.onReady(function() {
    MODx.load({ xtype: '[[+packageNameLower]]-page-home'});
});

[[+packageName]].page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: '[[+packageNameLower]]-panel-home'
            ,renderTo: '[[+packageNameLower]]-panel-home-div'
        }]
    }); 
    [[+packageName]].page.Home.superclass.constructor.call(this,config);
};
Ext.extend([[+packageName]].page.Home,MODx.Component);
Ext.reg('[[+packageNameLower]]-page-home',[[+packageName]].page.Home);