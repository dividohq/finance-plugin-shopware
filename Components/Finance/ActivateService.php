<?php
/**
 * File for RequestService class
 *
 * PHP version 7.1
 */

namespace dividoFinancePlugin\Components\Finance;

use dividoFinancePlugin\Components\Finance\Helper;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Environment;
use Divido\MerchantSDK\Models\Application;

/**
 * Helper service class to make application requests
 *
 * @category CategoryName
 * @package  dividoFinancePlugin
 * @since    File available since Release 1.0.0
 */
class ActivateService
{
    /**
     * Make an activate request via the SDK from the order
     *
     * @param String $application_id The application ID
     * @param String $total          The cart total
     * @param Array  $items          The items in the cart
     *
     * @return dividoFinancePlugin\Components\Finance\MerchantResponse
     */
    public static function activateApplication($application_id, $total, $items)
    {
        $apiKey = Helper::getApiKey();

        $environment = Helper::getEnvironment($apiKey);

        if (!$environment) {
            return false;
        }

        Helper::debug("Activating order '$application_id'", 'info');
        $httpClient = new \GuzzleHttp\Client();

        $guzzleClient = new \Divido\MerchantSDKGuzzle5\GuzzleAdapter($httpClient);

        $httpClientWrapper =  new \Divido\MerchantSDK\HttpClient\HttpClientWrapper(
            $guzzleClient,
            \Divido\MerchantSDK\Environment::CONFIGURATION[$environment]['base_uri'],
            $apiKey
        );

        $sdk = new Client(
            $httpClientWrapper,
            $environment
        );

        $application = (new \Divido\MerchantSDK\Models\Application())
            ->withId($application_id);

        $applicationActivation = (
            new \Divido\MerchantSDK\Models\ApplicationActivation()
        )
            ->withAmount($total)
            ->withReference("Order ".$application_id)
            ->withComment('Order was dispatched by merchant.')
            ->withOrderItems($items)
            ->withDeliveryMethod('delivery');

        // Create a new activation for the application.
        $response = $sdk->applicationActivations()->createApplicationActivation(
            $application,
            $applicationActivation
        );

        $applicationResponseBody = $response->getBody()->getContents();
        Helper::debug("Received response: ".$applicationResponseBody, 'info');
        $responseObj = json_decode($applicationResponseBody);

        $response = new MerchantResponse($responseObj);
        return $response;

    }
}