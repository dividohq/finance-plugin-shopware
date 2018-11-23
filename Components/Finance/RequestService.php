<?php

namespace FinancePlugin\Components\Finance;

use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Environment;
use Divido\MerchantSDK\Models\Application;

class RequestService
{

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

    public static function setBillingAddressFromUser(array $user) : array
    {
        Helper::debug('setting request billing address');

        $billing = $user['billingaddress'];
        $return = array(
            
        );
        Helper::debug('Billing address:' . serialize($return), 'info');

        return [$return];
    }

    public static function setOrderItemsFromBasket(array $basket)
    {
        $productsArray = array();
        foreach ($basket['content'] as $id => $product) {
            $row = [
                'name' => $product['articlename'],
                'quantity' => intval($product['quantity']),
                'price' => $product['price']*100,
            ];
            if ($product['modus'] == '0') {
                $row['plans'] = 
                    $product['additional_details']['attributes']['core']
                        ->get('finance_plans');
            }
            $productsArray[] = $row;
        }

        return $productsArray;
    }

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

    public static function makeRequest(\FinancePlugin\Models\Request $request)
    {
        $apiKey = Helper::getApiKey();
        $sdk = new Client(
            $apiKey,
            Helper::GetEnvironment($apiKey)
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
    }
}