<?php
/**
 * File for the Session class
 *
 * PHP version 5.6
 *
 */

namespace FinancePlugin\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use FinancePlugin\Components\Finance\Helper;

/**
 * @ORM\Table(name="s_plugin_FinancePlugin_sessions")
 * @ORM\Entity
 */
class Session extends ModelEntity
{
    private $table = 's_plugin_FinancePlugin_sessions';
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
     * @var integer $activated_on
     *
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $activated_on;

    /**
     * @var integer $cancelled_on
     *
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $cancelled_on;

    /**
     * @var integer $refunded_on
     *
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $refunded_on;

    /**
     * Array of the keys of fields we want to retain in the session table in
     * case the session times out before the customer completes the signing process
     */
    private $_retained_session_keys = array(
        'sUserData',
        'sBasket',
        'sAmount',
        'sPayment',
        'sDispatch'
    );

    /**
     * Compression method for session data. Currently either SERIAL or JSON
     */
    const COMPRESSION_METHOD = 'SERIAL';

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
     * Set session based on Shopwares saved variables
     *
     * @return void
     */
    public function setDataFromShopwareSession()
    {
        $session_data = Shopware()->Session()->sOrderVariables;
        $data = [];
        foreach ($this->_retained_session_keys as $key) {
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
    public function getActivatedOn()
    {
        return $this->activated_on;
    }

    /**
     * @param int $activated_on
     */
    public function setActivatedOn($activated_on)
    {
        $this->activated_on = $activated_on;
    }

    /**
     * @return int
     */
    public function getCancelledOn()
    {
        return $this->cancelled_on;
    }

    /**
     * @param int $cancelled_on
     */
    public function setCancelledOn($cancelled_on)
    {
        $this->cancelled_on = $cancelled_on;
    }

    /**
     * @return int
     */
    public function getRefundedOn()
    {
        return $this->refunded_on;
    }

    /**
     * @param int $refunded_on
     */
    public function setRefundedOn($refunded_on)
    {
        $this->refunded_on = $refunded_on;
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
     * Set the Plan variable
     *
     * @param string $plan
     *
     * @return void
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }


    /**
     * Retrieve session from database via session id
     * and set the class variables accordingly
     *
     * @param string     $id         The session ID
     * @param connection $connection An open DBAL DB connection
     *
     * @return boolean
     */
    public function retrieveFromDb($id, $connection)
    {
        $get_session_query = $connection->createQueryBuilder();
        $session_sql
            = "SELECT * FROM `".$this->table."` WHERE `id`= :id LIMIT 1";
        $session = $connection->fetchAll($session_sql, [':id' => $id]);
        if (isset($session[0])) {
            $this->id = $id;
            $this->orderNumber = $session[0]['orderNumber'];
            $this->transactionID = $session[0]['transactionID'];
            $this->key = $session[0]['key'];
            $this->data = $this->decompress($session[0]['data']);
            $this->plan = $session[0]['plan'];
            $this->deposit = $session[0]['deposit'];
            $this->ip_address = $session[0]['ip_address'];
            $this->created_on = $session[0]['created_on'];
            $this->activated_on = $session[0]['activated_on'];
            $this->cancelled_on = $session[0]['cancelled_on'];
            $this->refunded_on = $session[0]['refunded_on'];
            $this->status = $session[0]['status'];
            return true;
        } else return false;
    }

    /**
     * Store a session based on the class variables
     *
     * @param connection $connection An open DBAL DB query
     *
     * @return string
     */
    public function store($connection)
    {
        $ip_address
            = (!empty($this->ip_address))
            ? $this->ip_address
            : $_SERVER['REMOTE_ADDR'] ;

        $add_session_query = $connection->createQueryBuilder();
        $created_on = (!empty($this->created_on)) ? $this->createdon : time();
        $add_session_query
            ->insert($this->table)
            ->setValue('`orderNumber`', '?')
            ->setValue('`transactionID`', '?')
            ->setValue('`key`', '?')
            ->setValue('`status`', '?')
            ->setValue('`data`', '?')
            ->setValue('`plan`', '?')
            ->setValue('`deposit`', '?')
            ->setValue('`ip_address`', '?')
            ->setValue('`created_on`', '?')
            ->setValue('`activated_on`', '?')
            ->setValue('`cancelled_on`', '?')
            ->setValue('`refunded_on`', '?')
            ->setParameter(0, $this->orderNumber)
            ->setParameter(1, $this->transactionID)
            ->setParameter(2, $this->getKey())
            ->setParameter(3, $this->getStatus())
            ->setParameter(4, $this->compress($this->data))
            ->setParameter(5, $this->plan)
            ->setParameter(6, $this->deposit)
            ->setParameter(7, $ip_address)
            ->setParameter(8, $created_on)
            ->setParameter(9, $activated_on)
            ->setParameter(10, $cancelled_on)
            ->setParameter(11, $refunded_on);

        $add_session_query->execute();

        $this->id = $connection->lastInsertId();

        return $this->id;
    }

    /**
     * Update the session based on the class varaibles
     *
     * @param connection $connection An open DBAL DB connection
     *
     * @return void
     */
    public function update($connection)
    {
        if (!isset($this->id)) {
            Helper::Debug('Could not update session: No unique id to reference');
            return false;
        }

        $update_session_query = $connection->createQueryBuilder();
        $update_session_query->update($this->table);

        if (!is_null($this->orderNumber)) {
            $update_session_query
                ->set('`orderNumber`', ':orderNumber')
                ->setParameter(':orderNumber', $this->orderNumber);
        }

        if (!is_null($this->transactionID)) {
            $update_session_query
                ->set('`transactionID`', ':transactionID')
                ->setParameter(':transactionID', $this->transactionID);
        }

        if (!is_null($this->key)) {
            $update_session_query
                ->set('`key`', ':key')
                ->setParameter(':key', $this->key);
        }

        if (!is_null($this->status)) {
            $update_session_query
                ->set('`status`', ':status')
                ->setParameter(':status', $this->status);
        }

        if (!is_null($this->data)) {
            $update_session_query
                ->set('`data`', ':data')
                ->setParameter(':data', $this->compress($this->data));
        }

        if (!is_null($this->plan)) {
            $update_session_query
                ->set('`plan`', ':plan')
                ->setParameter(':plan', $this->plan);
        }

        if (!is_null($this->deposit)) {
            $update_session_query
                ->set('`deposit`', ':deposit')
                ->setParameter(':deposit', $this->deposit);
        }

        if (!is_null($this->ip_address)) {
            $update_session_query
                ->set('`ip_address`', ':ip_address')
                ->setParameter(':ip_address', $this->ip_address);
        }

        if (!is_null($this->created_on) ) {
            $update_session_query
                ->set('`created_on`', ':created_on')
                ->setParameter(':created_on', $this->created_on);
        }

        if (!is_null($this->activated_on) ) {
            $update_session_query
                ->set('`activated_on`', ':activated_on')
                ->setParameter(':activated_on', $this->activated_on);
        }

        if (!is_null($this->cancelled_on) ) {
            $update_session_query
                ->set('`cancelled_on`', ':cancelled_on')
                ->setParameter(':cancelled_on', $this->cancelled_on);
        }

        if (!is_null($this->refunded_on) ) {
            $update_session_query
                ->set('`refunded_on`', ':refunded_on')
                ->setParameter(':refunded_on', $this->refunded_on);
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
     * @return orderNumber (string) The number of the new Order stored in s_order
     */
    public function createOrder($device='')
    {
        $session = $this->getData();
        $basket = $session['sBasket'];
        $order = Shopware()->Modules()->Order();
        $order->sUserData = $session['sUserData'];
        $order->sComment = "";
        $order->sBasketData = $basket;
        $order->sAmount = $basket['sAmount'];
        $order->sAmountWithTax
            = !empty($basket['AmountWithTaxNumeric']) ? $basket['AmountWithTaxNumeric'] : $basket['AmountNumeric'];
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

    /**
     * Delete sessions based on an array of parameters
     *
     * @param connection $connection Open DBAL DB connection
     * @param array      $where      Array of sessions to match
     *
     * @return void
     */
    public static function delete($connection, $where)
    {
        $del_session_query = $connection->createQueryBuilder();
        $del_session_query
            ->delete(self::table)
            ->where("`id`='{$where['id']}'");

        foreach ($where as $key => $value) {
            $del_session_query
                ->where("{$key} = :{$key}")
                ->setParameter(":{$key}", $value);
        }

        return $del_session_query->execute();
    }

    /**
     * Find a session based on an array of parameters
     *
     * @param array      $criteria   The search criteria
     * @param connection $connection An open DBAL DB connection
     *
     * @return void
     */
    public static function findSessions($criteria, $connection)
    {
        $find_session_query = $connection->createQueryBuilder();
        $find_session_query->select(self::table);

        foreach ($criteria as $key=>$value) {
            $find_session_query
                ->where("`{$key}`= :{$key}")
                ->setParameter(":{$key}", $value);
        }

        $find_session_query->execute();
        return $find_session_query->fetch_all();
    }

    /**
     * Update a session based on it's reference key
     *
     * @param connection $connection    Open DBAL DB connection
     * @param array      $session       The updated parameters
     * @param string     $reference_key The unique key within the session array
     *
     * @return void
     */
    public static function updateByReference($connection, $session, $reference_key)
    {
        if (!isset($session[$reference_key])) {
            Helper::Debug(
                'Could not update session: Reference key not set or does not exist'
            );
            return false;
        }
        $update_session_query = $connection->createQueryBuilder();
        $update_session_query->update(self::table);

        foreach ($session as $key=>$value) {
            if ($key == $reference_key) {
                $update_session_query->where("`$key` = :$key");
            } else {
                $update_session_query->set("`$key`", ":$key");
            }
            $update_session_query->setParameter(":$key", $value);
        }

        return $update_session_query->execute();
    }

    /**
     * Wrapper function to overwrite if you wanted to,
     * say json_encode the data instead
     *
     * @param array $data Array of data to compress
     *
     * @return string
     */
    protected function compress($data)
    {
        switch(self::COMPRESSION_METHOD){
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
     *
     * @param string $data Data to uncompress
     *
     * @return void
     */
    protected function decompress($data)
    {
        switch(self::COMPRESSION_METHOD){
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