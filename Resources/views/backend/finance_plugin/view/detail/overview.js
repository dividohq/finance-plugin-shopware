//{block name="backend/order/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.FinancePlugin.view.detail.Overview', {
    /**
    * Override the customer detail window
    * @string
    */
    override: 'Shopware.apps.Order.view.detail.Overview',

    snippets:{
        activateOrder: '<?php $_smarty_tpl->smarty->_tag_stack[] = array('snippet', array('name'=>'activate_label','default'=>'Activate','namespace'=>'widgets / finance_plugin / backend / order / request')); $_block_repeat=true; echo Enlight_Components_Snippet_Resource::compileSnippetBlock(array('name'=>'activate_label','default'=>'Activate','namespace'=>'widgets / finance_plugin / backend / order / request'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
    Activate <? php $_block_content = ob_get_clean(); $_block_repeat=false; echo Enlight_Components_Snippet_Resource:: compileSnippetBlock(array('name'=> 'activate_label', 'default'=> 'Activate', 'namespace'=> 'widgets/finance_plugin/backend/order/request'), $_block_content, $_smarty_tpl, $_block_repeat); } array_pop($_smarty_tpl -> smarty -> _tag_stack);?>
        ',
        cancelOrder: '<?php $_smarty_tpl->smarty->_tag_stack[] = array('snippet', array('name'=>'cancel_label','default '=>'Cancel','namespace'=>'widgets / finance_plugin / backend / order / request')); $_block_repeat=true; echo Enlight_Components_Snippet_Resource::compileSnippetBlock(array('name'=>'cancel_label','default '=>'Cancel','namespace'=>'widgets / finance_plugin / backend / order / request'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
Cancel <? php $_block_content = ob_get_clean(); $_block_repeat = false; echo Enlight_Components_Snippet_Resource:: compileSnippetBlock(array('name'=> 'cancel_label', 'default'=> 'Cancel', 'namespace'=> 'widgets/finance_plugin/backend/order/request'), $_block_content, $_smarty_tpl, $_block_repeat); } array_pop($_smarty_tpl -> smarty -> _tag_stack);?>
        ',
        refundOrder: '<?php $_smarty_tpl->smarty->_tag_stack[] = array('snippet', array('name'=>'refund_label','default '=>'Refund','namespace'=>'widgets / finance_plugin / backend / order / request')); $_block_repeat=true; echo Enlight_Components_Snippet_Resource::compileSnippetBlock(array('name'=>'refund_label','default '=>'Refund','namespace'=>'widgets / finance_plugin / backend / order / request'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
Refund <? php $_block_content = ob_get_clean(); $_block_repeat = false; echo Enlight_Components_Snippet_Resource:: compileSnippetBlock(array('name'=> 'refund_label', 'default'=> 'Refund', 'namespace'=> 'widgets/finance_plugin/backend/order/request'), $_block_content, $_smarty_tpl, $_block_repeat); } array_pop($_smarty_tpl -> smarty -> _tag_stack);?>
        '
    },

    registerEvents: function(){
        this.addEvents('activateOrder', 'refundOrder', 'cancelOrder', 'updateFinance');
    },

    getEditFormButtons: function () {
        var me = this,
            buttons = [];

        buttons.push('->');
        var cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.edit.cancel,
            scope: me,
            cls: 'secondary',
            handler: function () {
                me.record.reject();
                me.loadRecord(me.record);
                me.attributeForm.loadAttribute(me.record.get('id'));
            }
        });
        buttons.push(cancelButton);

        var saveButton = Ext.create('Ext.button.Button', {
            text: me.snippets.edit.save,
            action: 'save-order',
            cls: 'primary',
            handler: function () {
                me.editForm.getForm().updateRecord(me.record);
                me.fireEvent('saveOverview', me.record, {
                    callback: function (order) {
                        me.attributeForm.saveAttribute(me.record.get('id'));
                        me.fireEvent('updateForms', order, me.up('window'));
                    }
                });
                me.fireEvent('updateFinance', me.record);
            }
        });

        //Create a order? Then display only the form panel
        if (!me.record.get('id')) {
            buttons.push(saveButton);
        } else {
            /*{if {acl_is_allowed privilege=update}}*/
            buttons.push(saveButton);
            /*{/if}*/
        }

        var activateFinanceButton = Ext.create('Ext.button.Button', {
            text: me.snippets.activateOrder,
            action: 'activate-order',
            cls: 'primary',
            hidden: true,
            handler: function () {
                me.fireEvent('activateOrder', me.record, this);
            },
            listeners: {
                beforeRender: {
                    fn: function () {
                        var btn = this;
                        Ext.Ajax.request({
                            url: '{url controller=FinancePlugin action="checkStatus"}',
                            method: 'GET',
                            params: {
                                orderId: me.record.get('id')
                            },
                            success: function (response) {
                                var data = Ext.decode(response.responseText);
                                if (data.status == 'READY') {
                                    btn.show();
                                }
                            }
                        });
                    }
                }
            }
        });

        var refundFinanceButton = Ext.create('Ext.button.Button', {
            text: me.snippets.refundOrder,
            action: 'refund-order',
            cls: 'primary',
            hidden: true,
            handler: function () {
                me.fireEvent('refundOrder', me.record, this);
            },
            listeners: {
                beforeRender: {
                    fn: function () {
                        var btn = this;
                        Ext.Ajax.request({
                            url: '{url controller=FinancePlugin action="checkStatus"}',
                            method: 'GET',
                            params: {
                                orderId: me.record.get('id')
                            },
                            success: function (response) {
                                var data = Ext.decode(response.responseText);
                                if (data.status == 'AWAITING-ACTIVATION') {
                                    btn.show();
                                }
                            }
                        });
                    }
                }
            }
        });

        var cancelFinanceButton = Ext.create('Ext.button.Button', {
            text: me.snippets.cancelOrder,
            action: 'cancel-order',
            cls: 'secondary',
            hidden: true,
            handler: function () {
                me.fireEvent('cancelOrder', me.record, this, {
                    callback: function (order) { }
                });
            },
            listeners: {
                beforeRender: {
                    fn: function () {
                        var btn = this;
                        Ext.Ajax.request({
                            url: '{url controller=FinancePlugin action="checkStatus"}',
                            method: 'GET',
                            params: {
                                orderId: me.record.get('id')
                            },
                            success: function (response) {
                                var data = Ext.decode(response.responseText);
                                if (data.status == 'READY') {
                                    btn.show();
                                }
                            }
                        });
                    }
                }
            }
        });

        buttons.push(refundFinanceButton);

        return buttons;
    }

});
//{/block}