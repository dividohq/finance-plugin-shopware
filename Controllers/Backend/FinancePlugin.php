<?php

use FinancePlugin\Components\Finance\Helper;

class Shopware_Controllers_Backend_FinancePlugin extends Shopware_Controllers_Backend_ExtJs {

    /**
     * Endpoint for an activation request on the backend
     *
     * @return void
     */
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
        $orderStatus = $_POST['orderStatus'];

        $activateStatus = Helper::getActivateStatus();

        if($orderStatus != $activateStatus){
            Helper::log('Order Status is not Activate Status', 'info');
            $this->View()->assign([
                'success' => true
            ]);
            return;
        }

        $activateService = $this->container->get('finance_plugin.activate_service');
        $orderService = $this->container->get('finance_plugin.order_service');

        $orderBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $order = $orderBuilder
            ->select(['orders.invoice_amount', 'orders.invoice_shipping', 'sessions.transactionID', 'sessions.activated_on', 'sessions.orderNumber'])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_sessions', 'sessions', 'orders.ordernumber = sessions.orderNumber')
            ->where('orders.id = :id')
            ->setParameter(':id', $orderId)
            ->execute()
            ->fetchAll();

        if($order[0]['activated_on'] > 0) {
            $this->View()->assign([
                'success' => true,
                'message' => "Order already activated"
            ]);
            return;
        }

        Helper::log('Activating Status', 'info');

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

        $activateResponse = $activateService::activateApplication($order[0]['transactionID'], $order_amount, $items);

        if($activateResponse->error == true) {
            $this->View()->assign([
                'success' => false,
                'message' => "Could not activate order: ".$activateResponse->message,
                "response" => $activateResponse
            ]);
            return;
        }

        $update_session_query = $this->get('dbal_connection')->createQueryBuilder();
        $update_session_query->update('s_sessions')
            ->where("`orderNumber` = :orderNumber")
            ->set("`activated_on`", ":now")
            ->setParameter(":orderNumber", $order[0]['orderNumber'])
            ->setParameter(":now", time())
            ->execute();

        $this->View()->assign([
            'success' => true,
            'message' => "Order activated",
            "response" => $activateResponse
        ]);
        return;
    }

    /**
     * Endpoint for a refund request from the backend
     *
     * @return void
     */
    public function refundOrderAction() {
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

        $refundService = $this->container->get('finance_plugin.refund_service');
        $orderService = $this->container->get('finance_plugin.order_service');

        $orderBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $order = $orderBuilder
            ->select(['orders.invoice_amount', 'orders.orderNumber', 'orders.invoice_shipping', 'sessions.transactionID'])
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

        $refundResponse = $refundService::refundApplication($order[0]['transactionID'], $order_amount, $items, $order[0]['orderNumber']);

        if($refundResponse->error == true) {
            $this->View()->assign([
                'success' => false,
                'message' => "Could not activate order: ".$refundResponse->message,
                "response" => $refundResponse
            ]);
            return;
        } else {
            $this->View()->assign([
                'success' => true,
                'message' => "Order Refunded",
                "response" => $refundResponse
            ]);
            return;
        }
    }

    /**
     * Endpoint for a cancel request from the backend
     *
     * @return void
     */
    public function cancelOrderAction() {
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

        $cancelService = $this->container->get('finance_plugin.cancel_service');
        $orderService = $this->container->get('finance_plugin.order_service');

        $orderBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $order = $orderBuilder
            ->select(['orders.invoice_amount', 'orders.orderNumber', 'orders.invoice_shipping', 'sessions.transactionID'])
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

        $cancelResponse = $cancelService::cancelApplication($order[0]['transactionID'], $order_amount, $items, $order[0]['orderNumber']);

        if($cancelResponse->error == true) {
            $this->View()->assign([
                'success' => false,
                'message' => "Could not cancel order: ".$cancelResponse->message,
                "response" => $cancelResponse
            ]);
            return;
        } else {
            $this->View()->assign([
                'success' => true,
                'message' => "Order Cancelled",
                "response" => $cancelResponse
            ]);
            return;
        }
    }

    public function checkStatusAction() {
        $webhookService = $this->container->get('finance_plugin.webhook_service');

        $orderId = $_GET['orderId'];

        $orderBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $order = $orderBuilder
            ->select(['sessions.status'])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_sessions', 'sessions', 'orders.ordernumber = sessions.orderNumber')
            ->where('orders.id = :id')
            ->setParameter(':id', $orderId)
            ->execute()
            ->fetchAll();

        if(isset($order[0]['status'])) {
            switch($order[0]['status']) {
                case $webhookService::PAYMENTSTATUSAWAITINGACTIVATION:
                    $status = $webhookService::STATUS_AWAITING_ACTIVATION;
                    break;
                case $webhookService::PAYMENTCANCELLED:
                    $status = $webhookService::STATUS_CANCELLED;
                    break;
                case $webhookService::PAYMENTSTATUSREFUNDED:
                    $status = $webhookService::STATUS_REFUNDED;
                    break;
                default:
                    $status = $webhookService::STATUS_READY;
                    break;
            }
        } else $status = 'N/A';

        $this->View()->assign([
            'status' => $status,
            'orderId' => $orderId
        ]);
        return;
    }

    public function getPlansAction()
    {
        $planBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $plans = $planBuilder
            ->select(['plans.id', 'plans.name'])
            ->from('s_plans', 'plans')
            ->execute()
            ->fetchAll();

        $data = [];
        foreach($plans as $plan) {
            $data[] = [
                'id'   => $plan['id'],
                'name' => $plan['name']
            ];
        }

        $this->view->assign([
            'data' => $data,
            'total' => count($data),
        ]);
    }
}