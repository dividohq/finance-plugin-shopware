<?php

namespace FinancePlugin\Components\Finance;

use FinancePlugin\Components\Finance\Helper;

class OrderService
{
    public function retrieveOrderFromDb($id, $connection){
        $get_order_query = $connection->createQueryBuilder();
        $get_order_query
            ->select('*')
            ->from('s_order')
            ->where('`id` = :id')
            ->setParameter(':id', $id)
            ->setMaxResult(1);
        $order = $get_order_query->execute()->fetchAll();

        if(isset($order[0])){
            return $order[0];
        }else return false;
    }

    public function saveOrder($order){
        $order->sCreateTemporaryOrder();
        $orderNumber = $order->sSaveOrder();
        if(!$orderNumber){
            Helper::Debug('Could not create order', 'warning');
        }
        return $orderNumber;
    }

    /**
     * Function to get the ID based on the transactionID and
     * temporaryID in the database. 
     *
     * @param string $transactionID
     * @param string $key
     * @param $connection
     * @return void
     */
    public function getId($transactionID, $key, $connection){
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

        if(isset($orders)){
          return $orders[0]['id'];
        }else return false;
    }

    public function updateOrder($connection, $order, $reference_key){
        if(!isset($order[$reference_key])){
            Helper::debug('Could not update order: Reference key not set or does not exist');
            return false;
        }
        $update_order_query = $connection->createQueryBuilder();
        $update_order_query->update('s_order');

        foreach($order as $key=>$value){
            if($key == $reference_key){
                $update_order_query->where("`$key` = :$key");
            }else{
                $update_order_query->set("`$key`",":$key");
            }
            $update_order_query->setParameter(":$key", $value);
        }

        return $update_order_query->execute();
    }

    public function findOrders($criteria, $connection){
        $find_order_query = $connection->createQueryBuilder();
        $find_order_query->select('*')->from('s_order');
        
        $first = true;
        foreach($criteria as $key=>$value){
            if($first){
                $find_order_query->where("`{$key}`= :{$key}");
                $first = false;
            }else $find_order_query->andWhere("`{$key}`= :{$key}");

            $find_order_query->setParameter(":{$key}",$value);
        }
        return $find_order_query->execute()->fetch_all();
    }

    public function persistOrderAttributes($id, $attributes){
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