<?php
/**
 * File for FinancePlugin class
 * 
 * PHP version 7.1
 */

use FinancePlugin\Components\Finance\PaymentService;
use FinancePlugin\Components\Finance\RequestService;
use FinancePlugin\Components\Finance\OrderService;
use FinancePlugin\Components\Finance\Helper;
use FinancePlugin\Models\Request;
use Shopware\Components\CSRFWhitelistAware;

require_once __DIR__ . '../../../vendor/autoload.php';

/**
 * Controller class which handles the payment flow
 * 
 * @category CategoryName
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class Shopware_Controllers_Frontend_FinancePlugin 
    extends Shopware_Controllers_Frontend_Payment 
    implements CSRFWhitelistAware
{
    const
        PLUGIN_VERSION = "0.0.0.1",
        PAYMENTSTATUSPAID = 12,
        PAYMENTSTATUSOPEN = 17,
        PAYMENTREVIEWNEEDED = 21,
        PAYMENTCANCELLED = 35,
        STATUS_PROPOSAL      = 'PROPOSAL',
        STATUS_ACCEPTED      = 'ACCEPTED',
        STATUS_ACTION_LENDER = 'ACTION-LENDER',
        STATUS_CANCELED      = 'CANCELED',
        STATUS_COMPLETED     = 'COMPLETED',
        STATUS_DEFERRED      = 'DEFERRED',
        STATUS_DECLINED      = 'DECLINED',
        STATUS_DEPOSIT_PAID  = 'DEPOSIT-PAID',
        STATUS_FULFILLED     = 'FULFILLED',
        STATUS_REFERRED      = 'REFERRED',
        STATUS_SIGNED        = 'SIGNED',
        STATUS_READY         = 'READY';


     /**
      * Order History Mesaages
      *
      * @category CategoryName
      *
      * @var array
      */
    public $historyMessages = array(
        self::STATUS_ACCEPTED      => 'Credit request accepted',
        self::STATUS_ACTION_LENDER => 'Lender notified',
        self::STATUS_CANCELED      => 'Application canceled',
        self::STATUS_COMPLETED     => 'Application completed',
        self::STATUS_DEFERRED      => 'Application deferred by Underwriter,
         waiting for new status',
        self::STATUS_DECLINED      => 'Applicaiton declined by Underwriter',
        self::STATUS_DEPOSIT_PAID  => 'Deposit paid by customer',
        self::STATUS_FULFILLED     => 'Credit request fulfilled',
        self::STATUS_REFERRED      => 'Credit request referred by Underwriter,
         waiting for new status',
        self::STATUS_SIGNED        => 'Customer have signed all contracts',
        self::STATUS_READY         => 'Order ready to Ship',

    );

    /**
     * Allows webhooks to reach server
     *
     * @return void
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'return',
            'webhook'
        ];
    }

    /**
     * Hooks into the pre dispatch method.
     *
     * Look into this for more info
     * https://developers.shopware.com/developers-guide/event-guide/
     *
     * @return void
     */
    public function preDispatch()
    {
        /*
        * @var \Shopware\Components\Plugin $plugin 
        */
        $plugin = $this->get('kernel')->getPlugins()['FinancePlugin'];
        $this->get('template')->addTemplateDir(
            $plugin->getPath() . '/Resources/views/'
        );
    }

    /**
     * Index action method.
     *
     * Forwards to the correct action.
     *
     * @return void
     */
    public function indexAction()
    {
        Helper::debug('Index view', 'info');
        return $this->redirect(['action' => 'finance', 'forceSecure' => false]);
    }

    /**
     * Direct action method.
     *
     * Collects the payment information and transmits it to the payment provider.
     *
     * @return void
     */
    public function directAction()
    {
        Helper::debug('Direct Action', 'info');
        
        $service = $this->container->get('finance_plugin.payment_service');
        $router = $this->Front()->Router();
        $apiKey = Helper::getApiKey();

        $user = $this->getUser();
        $customer = Helper::getFormattedCustomerDetails($user);
        
        $basket = $this->getBasket();
        $amount = $this->getAmount();
        $deposit_percentage = filter_var(
            $_POST['divido_deposit'], //TODO: Ubrand: Can't Change
            FILTER_SANITIZE_NUMBER_INT
        );
        $planId = filter_var(
            $_POST['divido_plan'], //TODO: Ubrand: Can't Change
            FILTER_SANITIZE_EMAIL
        );
        

        $deposit = (empty($deposit_percentage))
            ? 0
            : Helper::getDepositAmount(
                $deposit_percentage,
                $amount
            );
        
        
        $token = $service->createPaymentToken(
            $amount,
            $user['additional']['user']['customernumber']
        );
        
        $checkout_url= $router->assemble(
            ['action' => 'cancel', 'forceSecure' => true]
        );
        $session = new \FinancePlugin\Models\Session;
        $session->setKey($token);
        $session->setStatus(self::PAYMENTSTATUSOPEN);
        $session->setDataFromShopwareSession();
        $session->setPlan($planId);
        $session->setDeposit($deposit);

        $connection = $this->container->get('dbal_connection');
        $sessionId = $session->store($connection);
        
        $metadata = [
            'token' => $service->createPaymentToken(
                $amount, 
                $user['additional']['user']['customernumber']
            ),
            'amount' => $amount
        ];
        
        $response_url = $router->assemble(
            [
            'action' => 'webhook', 
            'forceSecure' => true
            ]
        );
        $redirect_url = $router->assemble(
            [
            'action' => 'return', 
            'forceSecure' => true
            ]
        );
        $redirect_url .= "?sid={$sessionId}&token={$token}";

        $request = new Request();
        $request->setFinancePlanId($planId);
        //$request->setMerchantChannelId($merchantChannelId);
        $request->setCountryId($user['additional']['country']['countryiso']);
        $request->setCurrencyId($basket['sCurrencyName']);
        $request->setApplicants(RequestService::setApplicantsFromUser($user));
        $request->setLanguageId(RequestService::getLanguageId());
        $request->setOrderItems(RequestService::setOrderItemsFromBasket($basket));
        $request->setDepositAmount($deposit*100);
        $request->setDepositPercentage($deposit_percentage/100);
        $request->setUrls(
            [
            'merchant_redirect_url' => $redirect_url,
            'merchant_checkout_url' => $checkout_url,
            'merchant_response_url' => $response_url
            ]
        );
        $response = RequestService::makeRequest($request);

        // Create session if request is okay and forward to the payment platform
        if (isset($response->error)) {
            $property = $response->context->property;
            $more = $response->context->more;
            Helper::debug(
                $response->message."(".$property.": ".$more.")",
                'error'
            );
            $this->forward('cancel');
        } else {
            $payload = $response->data;

            $session->setTransactionID($payload->id);
            $session->update($connection);
            
            $this->redirect($payload->urls->application_url);
        }
    }

    /**
     * Finance Action Method
     *
     * Allows user to select finance before redirecting
     *
     * @return void
     */
    public function financeAction()
    {
        Helper::debug('Finance view', 'info');
        header('Access-Control-Allow-Origin: *');
        
        $basket = $this->getBasket();
        $products = Helper::getOrderProducts($basket);

        $user = $this->getUser();
        $customer = Helper::getFormattedCustomerDetails($user);

        $displayWarning = [];
        $displayFinance = false;
        $amount = $this->getAmount();
        $minCartAmount = Helper::getCartThreshold();
        if ($amount >= $minCartAmount) {
            $displayFinance = true;
        } else {
            $displayWarning[] = 'Cart does not meet minimum Finance Requirement.';
        }

        if ($customer['address']!=$customer['shippingAddress']) {
            $displayFinance = false;
            $displayWarning[] = "Shipping and billing address must match.";
        }

        $apiKey = Helper::getApiKey();
        if (empty($apiKey)) {
            $displayFinance = false;
            $displayWarning[] =  "No Api Key Detected. Please contact the merchant.";
        }
        
        list($key,$stuff) = preg_split("/\./", $apiKey);
        $this->View()->assign('apiKey', $key);
        $this->View()->assign('title', Helper::getTitle());
        $this->View()->assign('description', Helper::getDescription());
        $this->View()->assign('amount', $amount);
        $this->View()->assign('prefix', '');
        $this->View()->assign('suffix', '');
        $this->View()->assign('displayForm', $displayFinance);
        $this->View()->assign('displayWarning', $displayWarning);
        $this->View()->assign(
            'basket_plans', 
            implode(",", Helper::getBasketPlans($products))
        );
    }

    /**
     * Return action method
     *
     * Gets the PaymentResponse,
     * Fetches the corresponding session,
     * Checks to see if the response token is valid for the session
     * Checks to see if the order is already complete
     * Completes the order or displays the completed order
     * or returns an appropriate response on failure
     * (Probably a bit too busy!)
     *
     * @return void
     */
    public function returnAction()
    {
        $paymentService = $this->container->get('finance_plugin.payment_service');
        $orderService = $this->container->get('finance_plugin.order_service');

        /**
         * A simple response object
         * 
         * @var FinancePlugin\Components\Finance\PaymentResponse $response  
         */
        $response = $paymentService->createPaymentResponse($this->Request());

        if (isset($response->sessionId) && isset($response->token)) {
            $sessionId = filter_var(
                $response->sessionId, 
                FILTER_SANITIZE_NUMBER_INT
            );
            $connection = $this->container->get('dbal_connection');
            $session = new \FinancePlugin\Models\Session;
            
            if ($session->retrieveFromDb($sessionId, $connection)) {
                $data = $session->getData();
                
                $customer_number 
                    = $data['sUserData']['additional']['user']['customernumber'];
                $amount = $data['sBasket']['sAmount'];
                /*
                /   If response token matches the information in the session 
                /   $service = /Components/Finance/PaymentService.php 
                */
                if ($paymentService->isValidToken(
                    $amount, 
                    $customer_number, 
                    $response->token
                )
                ) {
                    // If we haven't already generated the order already:
                    if (is_null($session->getOrderNumber())) {
                        $device = $this->Request()->getDeviceType();
                        $order = $session->createOrder($device);

                        $orderNumber = $orderService->saveOrder($order);
                        if ($orderNumber) {
                            $orderID = $orderService->getId(
                                $session->getTransactionID(),
                                $session->getKey(),
                                $connection
                            );
                            $order->setPaymentStatus(
                                $orderID, 
                                self::PAYMENTSTATUSPAID
                            );
                            
                            $data['ordernumber'] = $orderNumber;
                            $data['cleared'] = self::PAYMENTSTATUSPAID;
                            
                            // Persist information to display on order in backend
                            $attributePersister = $this->container->get(
                                'shopware_attribute.data_persister'
                            );

                            $attributeData = array(
                                'finance_id' => $session->getPlan(),
                                'deposit_value' => $session->getDeposit()
                            );
                            
                            $attributePersister->persist(
                                $attributeData,
                                's_order_attributes',
                                $orderID
                            );

                            $session->setOrderNumber($orderNumber);
                            $session->update($connection);

                            session_write_close();
                        } else {
                            $this->View()->assign('error', 'Could not create order');
                            $this->View()->assign(
                                'template', 
                                'frontend/finance_plugin/error.tpl'
                            );
                            return;
                        }
                    } else {
                        $data['ordernumber'] = $session->getOrderNumber();
                    }

                    /*
                    /   Assign the relevant stored session information 
                    /   to the appropriate Smarty variables
                    */
                    $this->sendDataToSmarty($data);
                    $this->View()->assign(
                        'template', 
                        'frontend/finance_plugin/success.tpl'
                    );
                } else {
                    $this->View()->assign('error', 'Invalid token.');
                    $this->View()->assign(
                        'template',
                        'frontend/finance_plugin/error.tpl'
                    );
                }
            } else {
                $this->View()->assign('template', 'frontend/finance_plugin/404.tpl');
            }
        }
        Helper::debug('Return action', 'info');
    }

    /**
     * Cancel action method
     *
     * @return void
     */
    public function cancelAction()
    {
    }

    /**
     * Call back
     *
     * A listener that can receive calls from 
     * the platform to update an order in shopware
     * In the shopware documentation this webhook=notify
     *
     * @return void
     */
    public function webhookAction()
    {
        Helper::debug('Webhook', 'info');

        /*
         * @var PaymentService $service 
         */
        $service = $this->container->get('finance_plugin.payment_service');
        $response = $service->createWebhookResponse($this->Request());
            
        if (!$response->status) {
            Helper::debug('No Response Status', 'error');
            die('no response');
        }

        //Helper::hmacSign();

        $transactionID = $response->proposal;
        $paymentUniqueID = $response->token;

        Helper::debug('Webhook data:'.serialize($response), 'error');
        Helper::debug('Webhook TransactionID:'.$transactionId, 'info');
        Helper::debug('Webhook Unique Payment ID:'.$paymentUniqueId, 'info');
        $message ='';

        switch ($response->status) {
        case self::STATUS_PROPOSAL:
            Helper::debug('Webhook: Proposal', 'info');
            $message ='Proposal Hook Success';
            $session_status = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_ACCEPTED:
            Helper::debug('Webhook: Accepted', 'info');
            $message ='Accepted Hook Success';
            $session_status = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_SIGNED:
            Helper::debug('Webhook: Signed', 'info');
            $message ='Signed Hook Success';
            $session_status = self::PAYMENTSTATUSPAID;
            break;

        case self::STATUS_DECLINED:
            Helper::debug('Webhook: Declined', 'info');
            $message ='Declined Hook Success';
            $order_status = self::PAYMENTREVIEWNEEDED;
            $session_status = self::PAYMENTREVIEWNEEDED;
            break;

        case self::STATUS_CANCELED:
            Helper::debug('Webhook: Canceled', 'info');
            $message ='Canceled Hook Success';
            $order_status = self::PAYMENTCANCELLED;
            $session_status = self::PAYMENTCANCELLED;
            break;

        case self::STATUS_DEPOSIT_PAID:
            Helper::debug('Webhook: Deposit Paid', 'info');
            $message ='Deposit Paid Hook Success';
            $session_status = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_ACTION_LENDER:
            Helper::debug('Webhook: Deposit Paid', 'info');
            break;
            
        case self::STATUS_COMPLETED:
            $message ='Completed';
            Helper::debug('Webhook: Completed', 'info');
            break;

        case self::STATUS_DEFERRED:
            $message ='Deferred Success';
            Helper::debug('Webhook: STATUS_DEFERRED', 'info');
            break;

        case self::STATUS_FULFILLED:
            $message ='STATUS_FULFILLED Success';
            Helper::debug('Webhook: STATUS_FULFILLED', 'info');
            break;

        case self::STATUS_REFERRED:
            $message ='Order Referred Success';
            Helper::debug('Webhook: Referred', 'info');
            break;

        default:
            $message ='Empty Hook';
            Helper::debug('Webhook: Empty webook', 'warning');
            break;
        }

        if (isset($order_status)) {
            $this->savePaymentStatus(
                $transactionID,
                $paymentUniqueID,
                $order_status
            );
        }
        
        if (isset($session_status)) {
            $connection = $this->container->get('dbal_connection');
            $session = new \FinancePlugin\Models\Session;
            $update = [
                "status" => $session_status,
                "transactionID" => $transactionID,
                "temporaryID" => $paymentUniqueID
            ];
            $session->updateByReference($connection, $update, 'temporaryID');
        }

        //update order based on whats sent through
        //use signmature to determine if basket is set
        //create order on signed
        $this->respond(true, $message, false);
        return;
    }

    /**
     * Take order information as received from s_finance_sessions table
     * and assign the data to the relevant Smarty variables
     * 
     * @param array $order The session information stored in 
     *                     `s_finance_sessions` table `data` column
     * 
     * @return void
     */
    protected function sendDataToSmarty($order)
    {
        foreach ($order as $key=>$value) {
            $this->View()->assign($key, $value);
        }
        $addresses['billing'] = $order['sUserData']['billingaddress'];
        $addresses['shipping'] = $order['sUserData']['shippingaddress'];
        $addresses['equal']
            = ($addresses['billing'] == $addresses['shipping']);
        $this->View()->assign('sAddresses', $addresses);
        $this->View()->assign('sOrderNumber', $order['ordernumber']);
        $this->View()->assign('sShippingcosts', $order['sBasket']['sShippingcosts']);
        $this->View()->assign('sAmountNet', $order['sBasket']['AmountNetNumeric']);
    }
}
