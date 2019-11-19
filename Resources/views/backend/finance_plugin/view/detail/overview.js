//{block name="backend/order/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.FinancePlugin.view.detail.Overview', {
    /**
    * Override the customer detail window
    * @string
    */
    override: 'Shopware.apps.Order.view.detail.Overview',

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
            text: "{s namespace='backend/order/request' name='activate_order_label'}Activate Order{/s}",
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
            text: "{s namespace='backend/order/request' name='refund_order_label'}Refund Order{/s}",
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
            text: "{s namespace='backend/order/request' name='cancel_order_label'}Cancel Order{/s}",
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
