<?php

/**
 * File containing OrderService class
 * 
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

use FinancePlugin\Components\Finance\Helper;

/**
 * Service helper class for shopware orders
 */
class OrderService
{
    /**
     * Retrieve order by order ID
     *
     * @param int    $id         The unique Order ID
     * @param object $connection Open dbal connection
     * 
     * @return boolean
     */
    public static function retrieveOrderFromDb($id, $connection)
    {
        $get_order_query = $connection->createQueryBuilder();
        $get_order_query
            ->select('*')
            ->from('s_order')
            ->where('`id` = :id')
            ->setParameter(':id', $id)
            ->setMaxResult(1);
        $order = $get_order_query->execute()->fetchAll();

        if (isset($order[0])) {
            return $order[0];
        }else return false;
        
    }

    /**
     * Save order in s_orders table
     *
     * @param array $order Array with order information
     * 
     * @return int
     */
    public static function saveOrder($order)
    {
        $order->sCreateTemporaryOrder();
        $orderNumber = $order->sSaveOrder();
        if (!$orderNumber) {
            Helper::Debug('Could not create order', 'warning');
        }
        return $orderNumber;
    }

    /**
     * Function to get the ID based on the transactionID and
     * temporaryID in the database. 
     *
     * @param string $transactionID The order ID
     * @param string $key           The session ID
     * @param object $connection    Open dbal connection
     * 
     * @return void
     */
    public static function getId($transactionID, $key, $connection)
    {
        $get_id_query = $connection->createQueryBuilder();
        $get_id_query
            ->select('id')
            ->from('s_order')
            ->where('`transactionID` = :transactionId')
            ->andWhere('`temporaryID` = :temporaryId')
            ->setParameter(':transactionId', $transactionID)
            ->setParameter(':temporaryId', $key)
            ->setMaxResults(1);
        $orders = $get_id_query->execute()->fetchAll($order_sql);

        if (isset($orders)) {
            return $orders[0]['id'];
        }else return false;
        
    }

    /**
     * Update the order in the s_orders table
     *
     * @param object $connection    Open dbal connection
     * @param array  $order         Array of fields to update
     * @param string $reference_key The key in the order array 
     * 
     * @return boolean
     */
    public static function updateOrder($connection, $order, $reference_key)
    {
        if (!isset($order[$reference_key])) {
            Helper::debug(
                'Could not update order: Reference key not set or does not exist'
            );
            return false;
        }
        $update_order_query = $connection->createQueryBuilder();
        $update_order_query->update('s_order');

        foreach ($order as $key=>$value) {
            if ($key == $reference_key) {
                $update_order_query->where("`$key` = :$key");
            } else {
                $update_order_query->set("`$key`", ":$key");
            }
            $update_order_query->setParameter(":$key", $value);
        }

        return $update_order_query->execute();
    }

    /**
     * Search s_orders basedon received criteria
     *
     * @param array  $criteria   The variables to search by
     * @param object $connection Open dbal connection
     * 
     * @return boolean
     */
    public static function findOrders($criteria, $connection)
    {
        $find_order_query = $connection->createQueryBuilder();
        $find_order_query->select('*')->from('s_order');
        
        $first = true;
        foreach ($criteria as $key=>$value) {
            if ($first) {
                $find_order_query->where("`{$key}`= :{$key}");
                $first = false;
            } else $find_order_query->andWhere("`{$key}`= :{$key}");

            $find_order_query->setParameter(":{$key}", $value);
        }
        return $find_order_query->execute()->fetch_all();
    }

    /**
     * Perfist received attributes in the shopware_attributes
     * table
     *
     * @param int   $id         Order ID
     * @param array $attributes The order attributes
     * 
     * @return Boolean
     */
    public static function persistOrderAttributes($id, $attributes)
    {
        $attributePersister = Shopware()->Container()->get(
            'shopware_attribute.data_persister'
        );
        
        return 
            $attributePersister->persist(
                $attributes,
                's_order_attributes',
                $id
            );

    }

}