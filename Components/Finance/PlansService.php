<?php
/**
 * File containing the PlansService class
 * 
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

require_once __DIR__ . '../../../vendor/autoload.php';

use FinancePlugin\Models\Plan;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Environment;
use Divido\MerchantSDK\Handlers\ApiRequestOptions;
use Divido\MerchantSDK\Exceptions\MerchantApiBadResponseException;

/**
 * Helper service to maintain finance plans
 * 
 * @category CategoryName
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class PlansService
{
    /**
     * Duration (in milliseconds) before next plan request
     * 
     * @var integer
     */
    const REFRESH_RATE = 7200;

    /**
     * Retrieve stored plans from the s_plans table
     *
     * @param integer $since The amount of time (in seconds) to lookup since
     *                       the plans were last requested
     * 
     * @return array
     */
    public static function getStoredPlans(int $since = self::REFRESH_RATE):array
    {
        $now = time();
        $recent_plans = Shopware()->Db()->query(
            'SELECT * FROM `s_plans` WHERE `updated_on` > ?', 
            [$now - $since]
        );

        if (!($recent_plans)) return [];
        else {
            $return = [];
            foreach ($recent_plans as $plan) {
                $planObj = new Plan;
                $planObj->setId($plan['id']);
                $planObj->setName($plan['name']);
                $planObj->setDescription($plan['description']);
                $planObj->setUpdatedOn($plan['updated_on']);
                $return[] = $planObj;
            }
            return $return;
        }
    }

    /**
     * Retrieve relevant plans from SDK by API Key
     *
     * @param string $apiKey The API key
     * 
     * @return array
     */
    public static function getPlansFromSDK(string $apiKey):PlansResponse
    {
        $environment = Helper::getEnvironment($apiKey);
        
        $sdk = new Client(
            $apiKey,
            $environment
        );
        $requestOptions = (new ApiRequestOptions());
        // Retrieve all finance plans for the merchant.
        try{
            $plans = $sdk->getAllPlans($requestOptions);

            $plans = $plans->getResources();
            $planObjArray = [];
            foreach ($plans as $plan) {
                $planObj = new Plan;
                $planObj->setId($plan->id);
                $planObj->setName($plan->description);
                $planObj->setDescription($plan->description);
                $planObjArray[] = $planObj;
            }
            
            $response = new PlansResponse($planObjArray);
            return $response;
        }catch(MerchantApiBadResponseException $e){
            $errorMessage = SDKErrorHandler::getMessage($e);
            $response = new PlansResponse([], true, $e->getCode(), $errorMessage);
            return $response;
        }
    }

    /**
     * Loop through all basket products for specific
     * finance plan instructions
     * Check if the plans currently exist on the Merchant portal
     * Use all live plans if no individual plans set
     * 
     * @param array $products Helper function array of products
     * 
     * @return array
     */
    public function getBasketPlans($products)
    {
        $apiKey = Helper::getApiKey();
        if (empty($apiKey)) {
            return [];
        }

        $plans_response = self::getPlansFromSDK($apiKey);
        $current_plans = [];
        if (true === $plans_response->error) {
            return [];
        }else{
            foreach ($plans_response->plans as $plan) {
                $current_plans[] = $plan->getId();
            }
        }

        $basket_plans = [];
        $individual_plans = false;
        foreach ($products as $product) {
            if (isset($product['plans'])) {
                $individual_plans = true;
                $product_plans = explode("|", $product['plans']);
                if (empty($basket_plans)) {
                    foreach ($product_plans as $plan) {
                        if (!empty($plan) && in_array($plan, $current_plans))
                            $basket_plans[] = $plan;
                    }
                } else {
                    if (!empty($product_plans)) {
                        foreach ($basket_plans as $k => $listed) {
                            if (!in_array($listed, $product_plans))
                                unset($basket_plans[$k]);
                        }
                    }
                }
            }
        }

        if (true == $individual_plans) {
            return $basket_plans;
        } else {
            return $current_plans;
        }

        
    }

    /**
     * Store array of plans in the s_plans table
     *
     * @param array $plans The plans to store
     * 
     * @return void
     */
    public static function storePlans(array $plans):void
    {
        $now = time();
        foreach ($plans as &$plan) {
            $plan->setUpdatedOn($now);
            $inserts[] = "(?,?,?,?)";
            $values[] = $plan->getId();
            $values[] = $plan->getName();
            $values[] = $plan->getDescription();
            $values[] = $plan->getUpdatedOn();
        }
        if (isset($inserts)) {
            self::clearPlans();
            $sql = 'INSERT INTO `s_plans` 
                        (`id`, `name`, `description`, `updated_on`) 
                    VALUES
                        '. implode(",", $inserts);

            Shopware()->Db()->query($sql, $values);
        }
    }

    /**
     * Clear all plans in the `s_plans` table
     *
     * @return boolean
     */
    public static function clearPlans()
    {
        // TODO: This needs to run only if the API Key changes
        if (Shopware()->Db()->query("TRUNCATE TABLE `s_plans`")) {
            Shopware()->Db()->query("UPDATE `s_articles_attributes` SET `finance_plans` = NULL");
            return true;
        } else return false;
    }
}