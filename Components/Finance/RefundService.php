<?php
/**
 * File for RequestService class
 *
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

use FinancePlugin\Components\Finance\Helper;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Environment;
use Divido\MerchantSDK\Models\Application;

/**
 * Helper service class to make application requests
 *
 * @category CategoryName
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class RefundService
{
    /**
     * Make a refund request via the merchant SDK
     *
     * @param string $application_id Divido Order Reference ID
     * @param int    $total          Amount (in pence) being refunded
     * @param array  $items          Array of items in the order
     * @param string $order_id       Merchant's Order Reference ID
     * @return void
     */
    public static function refundApplication($application_id, $total, $items, $order_id=null)
    {
        $order_id = $order_id ?? $application_id;

        $apiKey = Helper::getApiKey();

        $environment = Helper::getEnvironment($apiKey);

        if(!$environment) return false;

        Helper::debug("Refunding order '$application_id'", 'info');
        $httpClient = new \GuzzleHttp\Client();

        $guzzleClient = new \Divido\MerchantSDKGuzzle5\GuzzleAdapter($httpClient);

        $httpClientWrapper =  new \Divido\MerchantSDK\HttpClient\HttpClientWrapper($guzzleClient,
            \Divido\MerchantSDK\Environment::CONFIGURATION[$environment]['base_uri'],
            $apiKey
        );

        $sdk = new Client(
            $httpClientWrapper,
            $environment
        );

        $application = (new Application())
            ->withId($application_id);

        $applicationRefund = (new \Divido\MerchantSDK\Models\ApplicationRefund())
            ->withAmount($total)
            ->withReference("Order Ref.".$order_id)
            ->withComment('As per merchant request.')
            ->withOrderItems($items);

        // Create a new activation for the application.
        $response = $sdk->applicationActivations()->createApplicationActivation(
            $application,
            $applicationActivation
        );

        $applicationResponseBody = $response->getBody()->getContents();
        Helper::debug("Received response: ".$refundResponseBody, 'info');
        $responseObj = json_decode($applicationResponseBody);

        $response = new MerchantResponse($responseObj);
        return $response;

    }
}