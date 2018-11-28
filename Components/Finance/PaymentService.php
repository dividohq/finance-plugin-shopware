<?php
/**
 * File for Payment Service
 *
 * PHP version 5.5
 */

namespace FinancePlugin\Components\Finance;

/**
 * Payment Service class
 *
 * @category CategoryName
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class PaymentService
{

    /**
     * Token validation check
     *
     * @param integer $amount      The returned cart total
     * @param string  $customer_id The received unique customer id
     * @param string  $token       The received token
     * 
     * @return boolean
     */
    public function isValidToken($amount, $customer_id, $token)
    {
        return password_verify(
            $this->_createTokenContent($amount, $customer_id), $token
        );
    }

    /**
     * Generate a token from amount and customer id
     *
     * @param integer $amount     The cart total amount
     * @param string  $customerId Unique customer ID
     * 
     * @return string
     */
    private function _createTokenContent($amount,$customerId)
    {
        return implode('|', [$amount, $customerId]);
    }

    /**
     * Token Creator
     *
     * @param float $amount     Amount passed in
     * @param int   $customerId Customer detail
     *
     * @return string
     */
    public function createPaymentToken($amount, $customerId)
    {
        return password_hash(
            $this->_createTokenContent($amount, $customerId), PASSWORD_DEFAULT
        );
    }

    /**
     * Webhook Helper
     *
     * @param \Enlight_Controller_Request_Request $request Data received
     *
     * @return WebhookResponse
     */
    public function createWebhookResponse(
        \Enlight_Controller_Request_Request $request
    ) {

        $data = json_decode($request->getRawBody());

        $webhookResponse = new WebhookResponse();
    
        $webhookResponse->event       = $data->event;
        $webhookResponse->status      = $data->status;
        $webhookResponse->name        = $data->name;
        $webhookResponse->email       = $data->email;
        $webhookResponse->proposal    = $data->proposal;
        $webhookResponse->application = $data->application;
        $webhookResponse->signature   = $data->metadata->signature;
        $webhookResponse->token       = $data->metadata->token;
        $webhookResponse->amount      = $data->metadata->amount;

        return $webhookResponse;
    }

    /**
     * Create a response fom the received request
     * 
     * @param \Enlight_Controller_Request_Request $request Data received
     * 
     * @return PaymentResponse
     */
    public function createPaymentResponse(
        \Enlight_Controller_Request_Request $request
    ) {
        $response = new PaymentResponse();
        $response->sessionId = $request->getParam('sid', null);
        $response->status = $request->getParam('status', null);
        $response->token = $request->getParam('token', null);

        return $response;
    }

    
}
