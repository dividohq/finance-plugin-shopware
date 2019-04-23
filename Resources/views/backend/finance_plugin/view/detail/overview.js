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
            handler: function () {
                me.editForm.getForm().updateRecord(me.record);
                me.fireEvent('activateOrder', me.record, {
                    callback: function (order) {
                        console.log(order);
                        //me.fireEvent('activateOrder', order, me.up('window'));
                    }
                });
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