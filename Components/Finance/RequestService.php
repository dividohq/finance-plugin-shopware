<?php
/**
 * File for RequestService class
 * 
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

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
class RequestService
{

    /**
     * Use the shopware $user array to create an applicant
     *
     * @param array $user The shopware user array
     * 
     * @return array
     */
    public static function setApplicantsFromUser(array $user):array
    {
        Helper::debug('setting request applicant');

        $billing = $user['billingaddress'];
        $billingAddress = [
            'postcode' => $billing['zipcode'],
            'street' => $billing['street'],
            'town' => $billing['city']
        ];
        
        $shipping = $user['shippingaddress'];
        $shippingAddress = [
            'postcode' => $shipping['zipcode'],
            'street' => $shipping['street'],
            'town' => $shipping['city']
        ];

        $return = array(
            'firstName' => $billing['firstname'],
            'lastName' => $billing['lastname'],
            'email' => $user['additional']['user']['email'],
            'addresses' => [
                $billingAddress
            ],
            'shippingAddress' => $shippingAddress
        );
        Helper::debug('CustomerArray:' . serialize($return), 'info');

        return [$return];
    }

    /**
     * Search through basket for all products
     *
     * @param array $basket Array of products
     * 
     * @return array
     */
    public static function setOrderItemsFromBasket(array $basket)
    {
        $productsArray = array();
        foreach ($basket['content'] as $id => $product) {
            $row = [
                'name' => $product['articlename'],
                'quantity' => intval($product['quantity']),
                'price' => $product['price']*100,
            ];
            if ('0' == $product['modus']) {
                $row['plans']
                    = $product['additional_details']['attributes']['core']
                        ->get('finance_plans');
            }
            $productsArray[] = $row;
        }

        return $productsArray;
    }

    /**
     * Retrieve the shop language based on locale
     *
     * @return string
     */
    public static function getLanguageId()
    {
        $container = Shopware()->Container();
        $containerLocale = $container->get('Locale');
        $locale = $containerLocale->toString();
        /* Not sure which way is more efficient/correct!
        /
        $shopLocale = Shopware()->Shop()->getLocale();
        $locale = $shopLocale->getLocale();
        /
        */
        list($languageId, $dialect) = explode("_", $locale, 2);
        return $languageId;
    }

    /**
     * Make a request via the SDK by packaging our Request model
     *
     * @param \FinancePlugin\Models\Request $request Request Model
     * 
     * @return void
     */
    public static function makeRequest(\FinancePlugin\Models\Request $request)
    {
        $apiKey = Helper::getApiKey();
        $environment = Helper::GetEnvironment($apiKey);
        
        if ($environment) {
            $sdk = new Client(
                $apiKey,
                $environment
            );

            $application = (new Application())
                ->withCountryId($request->getCountryId())
                ->withCurrencyId($request->getCurrencyId())
                ->withLanguageId($request->getLanguageId())
                ->withFinancePlanId($request->getFinancePlanId())
                ->withApplicants($request->getApplicants())
                ->withOrderItems($request->getOrderItems())
                //->withDepositPercentage($request->getDepositPercentage())
                ->withDepositAmount($request->getDepositAmount())
                ->withFinalisationRequired($request->getFinalisationRequired())
                ->withMerchantReference($request->getMerchantReference())
                ->withUrls($request->getUrls());

            $response = $sdk->applications()->createApplication($application);

            $applicationResponseBody = $response->getBody()->getContents();
            
            return json_decode($applicationResponseBody);
        } else return false;
    }
}