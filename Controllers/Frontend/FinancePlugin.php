<?php
/**
 * File for FinancePlugin class
 *
 * PHP version 7.1
 */

use FinancePlugin\Components\Finance\PaymentService;
use FinancePlugin\Components\Finance\RequestService;
use FinancePlugin\Components\Finance\PlansService;
use FinancePlugin\Components\Finance\OrderService;
use FinancePlugin\Components\Finance\WebhookService;
use FinancePlugin\Components\Finance\Helper;
use FinancePlugin\Models\Request;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;

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

    const PLUGIN_VERSION = "1.0.0",
          API_ERROR_MSG
              = 'We are unable to process this order
          with the chosen payment method. Please choose another
          method via the <i>Change payment method</i> button.',
          SSA_DECLINE_MSG
              = 'Shared secret authentication did not authenticate.',
          NON_PAID_ERROR_MSG
              = 'This order is still waiting to receive payment confirmation.
          It may just be the case that the confirmation hasn\'t quite
          arrived yet. Please give it a couple of seconds and refresh
          this page. Please contact the merchant if the problem persists.',
          ORDER_CREATION_ERROR_MSG = 'Could not create order.',
          INVALID_TOKEN_ERROR_MSG = 'Invalid token.',
          NO_RESPONSE_ERROR_MSG = 'Received response did not include status.';



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

        require_once($plugin->getPath().'/vendor/autoload.php');
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
            $_POST['divido_deposit'],
            FILTER_SANITIZE_NUMBER_INT
        );
        $planId = filter_var(
            $_POST['divido_plan'],
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
        $session->setStatus(WebhookService::PAYMENTSTATUSOPEN);
        $session->setDataFromShopwareSession();
        $session->setPlan($planId);
        $session->setDeposit($deposit);

        $connection = $this->container->get('dbal_connection');
        $sessionId = $session->store($connection);

        $metadata = [
            'token' => $token,
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
        $request->setMetadata($metadata);
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
            $displayWarning[] =  self::API_ERROR_MSG;
        } else {
            $basket_plans = PlansService::getBasketPlans($apiKey, $products);
            if (empty($basket_plans)) {
                $displayFinance = false;
                $displayWarning[] = self::API_ERROR_MSG;
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
                implode(",", $basket_plans)
            );
        }
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
                if ($session->getStatus() == WebhookService::PAYMENTSTATUSPAID) {
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
                                    WebhookService::PAYMENTSTATUSPAID
                                );
                                $this->get('models')->flush($order);

                                $data['ordernumber'] = $orderNumber;
                                $data['cleared'] = WebhookService::PAYMENTSTATUSPAID;

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
                                $this->View()->assign(
                                    'error',
                                    self::ORDER_CREATION_ERROR_MSG
                                );
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
                        $this->View()->assign('error', self::INVALID_TOKEN_ERROR_MSG);
                        $this->View()->assign(
                            'template',
                            'frontend/finance_plugin/error.tpl'
                        );
                    }
                } else {
                    $this->View()->assign('error', self::NON_PAID_ERROR_MSG);
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
        $service = $this->container->get('finance_plugin.webhook_service');

        $response = $service->createWebhookResponse($this->Request());

        if (!$response->status) {
            Helper::debug('No Response Status', 'error');
            $code = 400;
            $response = array(
                'status' => 'error',
                'message' => self::NO_RESPONSE_ERROR_MSG,
                'platform' => 'Shopware',
                'plugin_version' => self::PLUGIN_VERSION,
            );
        } else {

            $sign = Helper::hmacSign();

            if (true === $sign) {

                $transactionID = $response->proposal;
                $paymentUniqueID = $response->token;

                Helper::debug('Webhook data:'.serialize($response), 'info');
                Helper::debug('Webhook TransactionID:'.$transactionId, 'info');
                Helper::debug('Webhook Unique Payment ID:'.$paymentUniqueId, 'info');

                $statusInfo = WebhookService::getStatusInfo($response->status);

                $connection = $this->container->get('dbal_connection');

                if (!is_null($statusInfo['order_status'])) {

                    $orderID = OrderService::getId(
                        $transactionID,
                        $paymentUniqueID,
                        $connection
                    );

                    if (!empty($orderID)) {
                        // get order
                        $modelManager = $this->get('models');
                        $order = $modelManager->find(Order::class, $orderID);
                        $currentStatus = $order->getPaymentStatus();
                        $order->setPaymentStatus(
                            $modelManager->find(Status::class, $statusInfo['order_status'])
                        );
                        $modelManager->flush($order);
                        Helper::debug(
                            'Updated Order Status of :'.$orderID.' from '.$currentStatus.' to '.$statusInfo['order_status'],
                            'info'
                        );
                    } else {
                        Helper::debug('Could not find order #'.$orderID.' with token '.$paymentUniqueID, 'error');
                    }
                }

                if (!is_null($statusInfo['session_status'])) {

                    $session = new \FinancePlugin\Models\Session;

                    $update = array(
                        "status" => $statusInfo['session_status'],
                        "transactionID" => $transactionID
                    );

                    $session->updateByReference(
                        $connection,
                        $update,
                        'transactionID'
                    );
                }

                $response = array(
                    'status' => $statusInfo['status'],
                    'message' => $statusInfo['message'],
                    'platform' => 'Shopware',
                    'plugin_version' => self::PLUGIN_VERSION,
                );
                $code = $statusInfo['code'];
            } else {
                $code = 400;
                $response = array(
                    'status' => 'ok',
                    'message' => self::SSA_DECLINE_MSG,
                    'platform' => 'Shopware',
                    'plugin_version' => self::PLUGIN_VERSION
                );

                $session = new \FinancePlugin\Models\Session;
                $update = array(
                    "status" => WebhookService::PAYMENTREVIEWNEEDED,
                    "transactionID" => $transactionID,
                    "temporaryID" => $paymentUniqueID
                );
                $session->updateByReference($connection, $update, 'temporaryID');
            }
        }

        $body = json_encode(
            $response,
            JSON_PRETTY_PRINT |
                JSON_HEX_TAG |
                JSON_HEX_APOS |
                JSON_HEX_QUOT |
                JSON_HEX_AMP
        );

        $this->Response()
            ->clearHeaders()
            ->clearRawHeaders()
            ->clearBody();
        $this->Response()->setBody($body);
        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setHttpResponseCode($code);
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
