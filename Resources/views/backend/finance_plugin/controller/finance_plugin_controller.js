// This is the controller


Ext.define('Shopware.apps.FinancePlugin.controller.FinancePluginController', {
    /**
     * Override the order main controller
     * @string
     */
    override: 'Shopware.apps.Order.controller.Main',

    init: function () {
        var me = this;

        me.control({
            'order-detail-window order-overview-panel': {
                activateOrder: me.onActivateOrder,
            }
        });
        // me.callParent will execute the init function of the overridden controller
        me.callParent(arguments);


    },

    onActivateOrder: function (record, obj) {
        Ext.Ajax.request({
            url: '{url controller=FinancePlugin action="activateOrder"}',
            method: 'POST',
            params: {
                orderId: record.get('id')
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    obj.setDisabled(true);
                    Shopware.Notification.createGrowlMessage('{s name=activationSuccess}Order Activated{/s}', status.message);
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=activationError}Order was not activated{/s}', status.message);
                }
            }
        });
    }
});