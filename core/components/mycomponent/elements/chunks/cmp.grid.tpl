/**
* JS file for [[+packageName]] extra
*
* Copyright [[+copyright]] [[+author]] [[+email]]
* Created on [[+createdon]]
*
[[+license]]
* @package [[+packageNameLower]]
*/

/* These are for LexiconHelper:
 $modx->lexicon->load('[[+packageNameLower]]:default');
 include '[[+packageNameLower]].class.php'
 */

[[+packageName]].grid.[[+Element]]s = function (config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config, {
        id: "my-[[+element]]-window"
        ,url: [[+packageName]].config.connector_url
        , baseParams: {
           action: 'mgr/[[+element]]/getlist'
           ,thread: config.thread
        }
        , pageSize: 300
        , fields: [
            {name:'id', sortType: Ext.data.SortTypes.asInt}
            , {name: 'name', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'category', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'description'}
         ]
        , paging: true
        , autosave: false
        , remoteSort: false
        , autoExpandColumn: 'description'
        , cls: '[[+packageNameLower]]-grid'
        , sm: this.sm
        , columns: [this.sm, {
            header: _('id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 50
        }, {
            header: _('name')
            ,dataIndex: 'name'
            ,sortable: true
            ,width: 100
                                                                                                           }, {
           header: _('category'),
            dataIndex: 'category',
            sortable: true,
            width: 120
        }, {
            header: _('description')
            , dataIndex: 'description'
            , sortable: false
            , width: 300
        }]
        ,viewConfig: {
            forceFit: true,
            enableRowBody: true,
            showPreview: true,
            getRowClass: function (rec, ri, p) {
                var cls = '[[+packageNameLower]]-row';

                if (this.showPreview) {
                    return cls + ' [[+packageNameLower]]-resource-expanded';
                }
                return cls + ' [[+packageNameLower]]-resource-collapsed';
            }
        }
        , tbar: [{
                text: 'Bulk Actions'
                , menu: this.getBatchMenu()
            }
            ,{xtype: 'tbspacer', width: 200}
            ,{
                xtype: 'button'
                , id: '[[+packageNameLower]]-[[+element]]s-reload'
                , text: 'Reload'
                , listeners: {
                    'click': {fn: this.reload[[+Element]]s, scope: this}
                }
            }
        ]
    });
    [[+packageName]].grid.[[+Element]]s.superclass.constructor.call(this, config)
};
Ext.extend([[+packageName]].grid.[[+Element]]s, MODx.grid.Grid, {
     reload[[+Element]]s: function () {
        this.getStore().baseParams = {
            action: 'mgr/[[+element]]/getList'
            ,orphanSearch: 'mod[[+Element]]'
        };

        this.getBottomToolbar().changePage(1);
        this.refresh();

    }
    , _showMenu: function (g, ri, e) {
        e.stopEvent();
        e.preventDefault();
        this.menu.record = this.getStore().getAt(ri).data;
        if (!this.getSelectionModel().isSelected(ri)) {
            this.getSelectionModel().selectRow(ri);
        }
        this.menu.removeAll();

        var m = [];
        if (this.menu.record.menu) {
            m = this.menu.record.menu;
            if (m.length > 0) {
                this.addContextMenuItem(m);
                this.menu.show(e.target);
            }
        } else {
            var z = this.getBatchMenu();

            for (var zz = 0; zz < z.length; zz++) {
                this.menu.add(z[zz]);
            }
            this.menu.show(e.target);
        }
    }
    , getSelectedAsList: function () {
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;

        var cs = '';
        for (var i = 0; i < sels.length; i++) {
            cs += ',' + sels[i].data.id;
        }
        cs = Ext.util.Format.substr(cs, 1);
        return cs;
    }

    , changeCategory: function (btn, e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {ids: cs};
        if (!this.changeCategoryWindow) {
            this.changeCategoryWindow = MODx.load({
                  xtype: '[[+packageNameLower]]-[[+element]]-window-change-category'
                  , record: r
                  , listeners: {
                    'success': {fn: function (r) {
                        // this.refresh();
                        var sels = this.getSelectionModel().getSelections();
                        var cat = Ext.getCmp('[[+packageNameLower]]-[[+element]]-category-combo').lastSelectionText;
                        var s = this.getStore();
                        for (var i = 0; i < sels.length; i = i + 1) {
                            var id = sels[i].get('id');
                            var ri = id;
                            var record = s.getById(ri);
                            record.set("category", cat);
                            record.commit();
                        }
                        this.getSelectionModel().clearSelections(false);
                    }, scope: this}
                }
                                                  });
        }
        this.changeCategoryWindow.setValues(r);
        this.changeCategoryWindow.show(e.target);
        return true;
    }
    , [[+element]]Remove: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        MODx.msg.confirm({
             title: _('[[+packageNameLower]]_delete~~Delete Objects')
             , text: _('[[+packageNameLower]]_confirm_delete~~Are you sure?')
             , url: this.config.url
             , params: {
                action: 'mgr/[[+element]]/remove'
                , ids: cs
            }
            , listeners: {
                'success': {fn: function (r) {
                    // this.refresh();
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {

                        var id = sels[i].get('id');
                        var ri = id;
                        var record = s.getById(ri);
                        s.remove(record);
                    }
                }
                , scope: this}
                , 'failure': {fn: function (r) {
                    MODx.msg.alert();
                }
                , scope: this}
            }
        });
        return true;
    }

    , getBatchMenu: function () {
        var bm = [];
        bm.push(
            {
                text: _('new_category')
                , handler: this.changeCategory
                , scope: this
            }
            , '-'
            , {
                text: _('remove_[[+element]]')+'(s)'
                ,handler: this.[[+element]]Remove
                , scope: this
            });
        return bm;
    }
});
Ext.reg('[[+packageNameLower]]-grid-[[+element]]', [[+packageName]].grid.[[+Element]]s);


[[+packageName]].window.ChangeCategory = function (config) {
    var grid = Ext.getCmp('my-[[+element]]-window');
    var selectionModel = grid.getSelectionModel();
    var selectedRecords = grid.getSelectedAsList();
    config = config || {};
    Ext.applyIf(config, {
        title: _('new_category')
        , url: [[+packageName]].config.connector_url
        , baseParams: {
            action: 'mgr/[[+element]]/changecategory'
            }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: '[[+element]]s'
            ,value: selectedRecords
        },{
            xtype: 'modx-combo-category'
            ,id: '[[+packageNameLower]]-[[+element]]-category-combo'
            ,fieldLabel: _('category')
            ,name: 'category'
            ,hiddenName: 'category'
            ,anchor: '90%'
        }]
    });
    [[+packageName]].window.ChangeCategory.superclass.constructor.call(this, config);
};
Ext.extend([[+packageName]].window.ChangeCategory, MODx.Window);
Ext.reg('[[+packageNameLower]]-[[+element]]-window-change-category', [[+packageName]].window.ChangeCategory);
