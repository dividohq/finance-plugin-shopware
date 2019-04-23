<?php

use FinancePlugin\Components\Finance\Helper;
use Divido\MerchantSDK\Environment;
use FinancePlugin\Components\Finance\ActivateService;

class Shopware_Controllers_Backend_FinancePlugin extends Shopware_Controllers_Backend_ExtJs {


    public function activateOrderAction() {
        /**
         * Autoload the vendor files first
         */
        $plugin = $this->get('kernel')->getPlugins()['FinancePlugin'];
        $this->get('template')->addTemplateDir(
            $plugin->getPath() . '/Resources/views/'
        );

        require_once($plugin->getPath().'/vendor/autoload.php');
        /**
         * Autoload the vendor files first
         */

        $orderId = $_POST['orderId'];

        $activateService = $this->container->get('finance_plugin.activate_service');
        $orderService = $this->container->get('finance_plugin.order_service');

        $orderBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $order = $orderBuilder
            ->select(['orders.invoice_amount', 'orders.invoice_shipping', 'sessions.transactionID'])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_sessions', 'sessions', 'orders.ordernumber = sessions.orderNumber')
            ->where('orders.id = :id')
            ->setParameter(':id', $orderId)
            ->execute()
            ->fetchAll();

        $itemBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $itemList = $itemBuilder
            ->select(['item.name', 'item.price', 'item.quantity'])
            ->from('s_order_details', 'item')
            ->where('item.orderID = :id')
            ->setParameter(':id', $orderId)
            ->execute()
            ->fetchAll();

        $items = [];
        foreach($itemList as $item) {
            $items[] = [
                'name' => $item['name'],
                'quantity' => intval($item['quantity']),
                'price' => $item['price']*100
            ];
        }

        if($order[0]['invoice_shipping'] > 0) {
            $items[] = [
                'name' => 'Shipping',
                'quantity' => 1,
                'price' => $order[0]['invoice_shipping']*100
            ];
        }

        $order_amount = $order[0]['invoice_amount']*100;

        $activateResponse = ActivateService::activateApplication($order[0]['transactionID'], $order_amount, $items);

        if($activateResponse->error == true) {
            $this->View()->assign([
                'success' => false,
                'message' => "Could not activate order: ".$activateResponse->message,
                "response" => $activateResponse
            ]);
            return;
        } else {
            $this->View()->assign([
                'success' => true,
                'message' => "Order activated",
                "response" => $activateResponse
            ]);
            return;
        }
    }
}