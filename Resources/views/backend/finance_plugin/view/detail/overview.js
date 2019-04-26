//{block name="backend/order/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.FinancePlugin.view.detail.Overview', {
    /**
    * Override the customer detail window
    * @string
    */
    override: 'Shopware.apps.Order.view.detail.Overview',

    registerEvents: function(){
        this.addEvents('activateOrder');
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
                            method: 'POST',
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

        var buttons = me.getEditFormButtons();
        buttons.push(activateButton);

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: buttons
        });



        return me.toolbar;
    },

});
//{/block}