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

[[+packageName]].panel.Home = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,items: [{
            html: '<h2>'+'[[+packageNameLower]]'+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,defaults: { border: false ,autoHeight: true }
            ,border: true
            ,stateful: true
            ,stateId: '[[+packageNameLower]]-home-tabpanel'
            ,stateEvents: ['tabchange']
            ,getState:function() {
                return {activeTab:this.items.indexOf(this.getActiveTab())};
            }
            ,items: [{
                title: _('snippets')
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+'Demo only . . . grid will change, but no real action is taken'+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                },{
                    xtype: '[[+packageNameLower]]-grid-snippet'
                    ,preventRender: true
                }]
            },{
                title: _('chunks')
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+'Demo only . . . grid will change, but no real action is taken'+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                },{
                    xtype: '[[+packageNameLower]]-grid-chunk'
                    ,preventRender: true
                }]
            }]
        }]
    });
    [[+packageName]].panel.Home.superclass.constructor.call(this,config);
};
Ext.extend([[+packageName]].panel.Home,MODx.Panel);
Ext.reg('[[+packageNameLower]]-panel-home',[[+packageName]].panel.Home);
        