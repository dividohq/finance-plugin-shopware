<?php
/**
 * Payment Service
 *
 * PHP version 5.5
 *
 * @category  CategoryName
 * @package   FinancePlugin
 * @since     File available since Release 1.0.0
 */
namespace FinancePlugin\Components\Finance;

class PaymentService
{

    /**
     * Token Checker
     *
     * @param PaymentResponse $response Payment response object
     * @param string          $token    passed token
     *
     * @return bool
     */
    public function isValidToken($amount, $customer_id, $token)
    {
        return password_verify($this->createTokenContent($amount,$customer_id), $token);
    }

    private function createTokenContent($amount,$customerId){
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
        return password_hash($this->createTokenContent($amount,$customerId), PASSWORD_DEFAULT);
    }

    /**
     * Webhook Helper
     *
     * @param \Enlight_Controller_Request_Request $request
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
     * @param $request \Enlight_Controller_Request_Request
     * @return PaymentResponse
     */
    public function createPaymentResponse(
        \Enlight_Controller_Request_Request $request
    ){
        $response = new PaymentResponse();
        $response->sessionId = $request->getParam('sid', null);
        $response->status = $request->getParam('status', null);
        $response->token = $request->getParam('token', null);

        return $response;
    }

    
}
