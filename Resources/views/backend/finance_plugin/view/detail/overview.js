//{block name="backend/order/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.FinancePlugin.view.detail.Overview', {
    /**
    * Override the customer detail window
    * @string
    */
    override: 'Shopware.apps.Order.view.detail.Overview',

    registerEvents: function(){
        this.addEvents('activateOrder', 'refundOrder');
    },

    createToolbar: function () {
        var me = this;

        var activateButton = Ext.create('Ext.button.Button', {
            text: "activate",
            action: 'activate-order',
            cls: 'primary',
            hidden: true,
            handler: function () {
                me.fireEvent('activateOrder', me.record, this, {
                    callback: function (order) {
                        console.log(order);
                        //me.fireEvent('activateOrder', order, me.up('window'));
                    }
                });
            },
            listeners: {
                beforeRender: {
                    fn: function(){
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

        var refundButton = Ext.create('Ext.button.Button', {
            text: "refund",
            action: 'refund-order',
            cls: 'primary',
            hidden: true,
            handler: function () {
                me.fireEvent('refundOrder', me.record, this, {
                    callback: function (order) {}
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
                                if (data.status == 'AWAITING-ACTIVATION') {
                                    btn.show();
                                }
                            }
                        });
                    }
                }
            }
        });

        var buttons = me.getEditFormButtons();
        buttons.push(activateButton);
        buttons.push(refundButton);

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: buttons
        });



        return me.toolbar;
    },

});
//{/block}