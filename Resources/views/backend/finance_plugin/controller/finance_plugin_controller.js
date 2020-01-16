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
                refundOrder: me.onRefundOrder,
                cancelOrder: me.onCancelOrder,
                updateFinance: me.onUpdateFinance
            }
        });
        // me.callParent will execute the init function of the overridden controller
        me.callParent(arguments);


    },

    onUpdateFinance: function (record) {
        console.log(record);
        Ext.Ajax.request({
            url: '{url controller=FinancePlugin action="updateFinance"}',
            method: 'POST',
            params: {
                orderId: record.get('id'),
                orderStatus: record.get('status')
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    if(status.message !== null) {
                        Shopware.Notification.createGrowlMessage('{s name=activationSuccess}Order Updated{/s}', status.message);
                    }
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=activationError}Order Not Updated{/s}', status.message);
                }
            }
        });
    },

    onActivateOrder: function (record) {
        console.log(record);
        Ext.Ajax.request({
            url: '{url controller=FinancePlugin action="activateOrder"}',
            method: 'POST',
            params: {
                orderId: record.get('id'),
                orderStatus: record.get('status')
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    if(status.message !== null) {
                        Shopware.Notification.createGrowlMessage("{'Order Activated'|snippet:'activation_success_msg':'backend/order/response'}", status.message);
                    }
                } else {
                    Shopware.Notification.createGrowlMessage("{'Order not Activated'|snippet:'activation_error_msg':'backend/order/response'}", status.message);
                }
            }
        });
    },

    onRefundOrder: function (record, obj) {
        Ext.Ajax.request({
            url: '{url controller=FinancePlugin action="refundOrder"}',
            method: 'POST',
            params: {
                orderId: record.get('id')
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    obj.setDisabled(true);
                    Shopware.Notification.createGrowlMessage("{'Order Refunded'|snippet:'order_refund_success_msg':'backend/order/response'}", status.message);
                } else {
                    Shopware.Notification.createGrowlMessage("{'Order not Refunded'|snippet:'refund_error_msg':'backend/order/response'}", status.message);
                }
            }
        });
    },

    onCancelOrder: function (record, obj) {
        Ext.Ajax.request({
            url: '{url controller=FinancePlugin action="cancelOrder"}',
            method: 'POST',
            params: {
                orderId: record.get('id')
            },
            success: function (response) {
                var status = Ext.decode(response.responseText);
                if (status.success) {
                    obj.setDisabled(true);
                    Shopware.Notification.createGrowlMessage("{'Order Cancelled'|snippet:'cancellation_success_msg':'backend/order/response'}", status.message);
                } else {
                    Shopware.Notification.createGrowlMessage("{'Order not Cancelled'|snippet:'cancellation_error_msg':'backend/order/response'}", status.message);
                }
            }
        });
    }
});