<?php

namespace FinancePlugin\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use FinancePlugin\Components\Finance\Helper;

/**
 * @ORM\Table(name="s_sessions")
 * @ORM\Entity
 */
class Session extends ModelEntity
{
    private $table = 's_sessions';
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", length=8, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $orderNumber
     *
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $orderNumber;
    
    /**
     * @var integer $status
     *
     * @ORM\Column(type="integer", length=2, nullable=false)
     */
    private $status;

    /**
     * @var string $transactionID
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $transactionID;

    /**
     * @var string $key
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $key;

    /**
     * @var string $data
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $data;

    /**
     * @var string $plan
     *
     * @ORM\Column(type="string", length=25, nullable=false)
     */
    private $plan;

    /**
     * @var string $deposit
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $deposit;

    /**
     * @var string $ip_address
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $ip_address;

    /**
     * @var integer $created_on
     *
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $created_on;

    /**
     * Array of the keys of fields we want to retain in the session table in
     * case the session times out before the customer completes the signing process
     */
    private $retained_session_keys = array(
        'sUserData',
        'sBasket',
        'sAmount',
        'sPayment',
        'sDispatch'
    );

    private const session_table = 's_sessions';

    /**
     * Compression method for session data. Currently either SERIAL or JSON
     */
    private const compression_method = 'SERIAL';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    /**
     * @param string $transactionID
     */
    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * 
     */
    public function setDataFromShopwareSession(){
        $session_data = Shopware()->Session()->sOrderVariables;
        $data = [];
        foreach($this->retained_session_keys as $key){
            if(isset($session_data[$key]))
               $data[$key] = $session_data[$key];
        }
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;
    }

    /**
     * @return int
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * @param int $created_on
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
    }
    
    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param string $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return int
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param int $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }


    /**
     * Retrieve session from database via session id
     */
    public function retrieveFromDb($id, $connection){
        $get_session_query = $connection->createQueryBuilder();
        $session_sql = "SELECT * FROM `".self::session_table."` WHERE `id`= :id LIMIT 1";
        $session = $connection->fetchAll($session_sql,[':id' => $id]);
        if(isset($session[0])){
            $this->id = $id;
            $this->orderNumber = $session[0]['order_number'];
            $this->transactionID = $session[0]['transactionID'];
            $this->key = $session[0]['key'];
            $this->data = $this->decompress($session[0]['data']);
            $this->plan = $session[0]['plan'];
            $this->deposit = $session[0]['deposit'];
            $this->ip_address = $session[0]['ip_address'];
            $this->created_on = $session[0]['created_on'];
            $this->status = $session[0]['status'];
            return true;
        }else return false;
    }

    public function store($connection){
        $ip_address = (!empty($this->ip_address)) ? $this->ip_address : $_SERVER['REMOTE_ADDR'] ;
        $add_session_query = $connection->createQueryBuilder();
        $created_on = (!empty($this->created_on)) ? $this->createdon : time();
        $add_session_query
            ->insert(self::session_table)
            ->setValue('`orderNumber`','?')
            ->setValue('`transactionID`','?')
            ->setValue('`key`','?')
            ->setValue('`status`','?')
            ->setValue('`data`','?')
            ->setValue('`plan`','?')
            ->setValue('`deposit`','?')
            ->setValue('`ip_address`','?')
            ->setValue('`created_on`','?')
            ->setParameter(0,$this->orderNumber)
            ->setParameter(1,$this->transactionID)
            ->setParameter(2,$this->getKey())
            ->setParameter(3,$this->getStatus())
            ->setParameter(4,$this->compress($this->data))
            ->setParameter(5,$this->plan)
            ->setParameter(6,$this->deposit)
            ->setParameter(7,$ip_address)
            ->setParameter(8,$created_on);
        
        $add_session_query->execute();

        $this->id = $connection->lastInsertId();
        
        return $this->id;
    }

    public function update($connection){
        if(!isset($this->id)){
            Helper::Debug('Could not update session: No unique id to reference');
            return false;
        }

        $update_session_query = $connection->createQueryBuilder();
        $update_session_query->update(self::session_table);
        
        if(!is_null($this->orderNumber)){
            $update_session_query
                ->set('`orderNumber`',':orderNumber')
                ->setParameter(':orderNumber', $this->orderNumber);
        }

        if(!is_null($this->transactionID)){
            $update_session_query
                ->set('`transactionID`',':transactionID')
                ->setParameter(':transactionID', $this->transactionID);
        }

        if(!is_null($this->key)){
            $update_session_query
                ->set('`key`',':key')
                ->setParameter(':key', $this->key);
        }

        if(!is_null($this->status)){
            $update_session_query
                ->set('`status`',':status')
                ->setParameter(':status', $this->status);
        }

        if(!is_null($this->data)){
            $update_session_query
                ->set('`data`',':data')
                ->setParameter(':data', $this->compress($this->data));
        }

        if(!is_null($this->plan)){
            $update_session_query
                ->set('`plan`',':plan')
                ->setParameter(':plan', $this->plan);
        }

        if(!is_null($this->deposit)){
            $update_session_query
                ->set('`deposit`',':deposit')
                ->setParameter(':deposit', $this->deposit);
        }

        if(!is_null($this->ip_address)){
            $update_session_query
                ->set('`ip_address`',':ip_address')
                ->setParameter(':ip_address', $this->ip_address);
        }

        if(!is_null($this->created_on)){
            $update_session_query
                ->set('`created_on`',':created_on')
                ->setParameter(':created_on', $this->created_on);
        }

        $update_session_query
            ->where('`id` = :id')
            ->setParameter(':id', $this->id);
        
        return $update_session_query->execute();
    }

    /**
     * Generate an order based on the session data stored in the session table
     * 
     * @param string $device The type of device used when making this request
     * 
     * @return orderNumber (string) The order number of the new Order stored in s_order
     */
    public function createOrder($device=''){
        $session = $this->getData();
        $basket = $session['sBasket'];
        $order = Shopware()->Modules()->Order();
        $order->sUserData = $session['sUserData'];
        $order->sComment = "";
        $order->sBasketData = $basket;
        $order->sAmount = $basket['sAmount'];
        $order->sAmountWithTax = 
            !empty($basket['AmountWithTaxNumeric']) ? $basket['AmountWithTaxNumeric'] : $basket['AmountNumeric'];
        $order->sAmountNet = $basket['AmountNetNumeric'];
        $order->sShippingcosts = $basket['sShippingcosts'];
        $order->sShippingcostsNumeric = $basket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $basket['sShippingcostsNet'];
        $order->bookingId = $this->getTransactionId();
        $order->dispatchId = Shopware()->Session()->sDispatch;
        $order->sNet = empty($session['sUserData']['additional']['charge_vat']);
        $order->uniqueID = $this->getKey();
        $order->deviceType = $device;
        
        return $order;
    }

    public static function delete($connection, $where){
        $del_session_query = $connection->createQueryBuilder();
        $del_session_query->delete(self::session_table)->where("`id`='{$where['id']}'");
        
        foreach($where as $key => $value)
            $del_session_query->where("{$key} = :{$key}")->setParameter(":{$key}",$value);
        
        return $del_session_query->execute();
    }

    public static function findSessions($criteria,$connection){
        $find_session_query = $connection->createQueryBuilder();
        $find_session_query->select(self::session_table);
        
        foreach($criteria as $key=>$value)
            $find_session_query->where("`{$key}`= :{$key}")->setParameter(":{$key}",$value);
        
        $find_session_query->execute();
        return $find_session_query->fetch_all();
    }

    public static function updateByRef($connection, $session, $reference_key){
        if(!isset($session[$reference_key])){
            Helper::Debug('Could not update session: Reference key not set or does not exist');
            return false;
        }
        $update_session_query = $connection->createQueryBuilder();
        $update_session_query->update(self::session_table);

        foreach($order as $key=>$value){
            if($key == $reference_key){
                $update_session_query->where("`$key` = :$key");
            }else{
                $add_session_query->set("`$key`",":$key");
            }
            $add_session_query->setParameter(":$key", $value);
        }

        return $add_session_query->execute();
    }

    /**
     * Wrapper function to overwrite if you wanted to,
     * say json_encode the data instead
     */
    protected function compress($data){
        switch(self::compression_method){
            case 'JSON':
                $return = json_encode($data);
                break;
            case 'SERIAL':
            default:
                $return = serialize($data);
                break;
        }
        return $return;
    }

    /**
     * Wrapper function to overwrite if you wanted to,
     * say json_unencode the data instead
     */
    protected function decompress($data){
        switch(self::compression_method){
            case 'JSON':
                $return = json_decode($data);
                break;
            case 'SERIAL':
            default:
                $return = unserialize($data);
                break;
        }
        return $return;
    }
}