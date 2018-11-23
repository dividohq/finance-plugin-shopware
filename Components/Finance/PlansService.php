<?php

namespace FinancePlugin\Components\Finance;

use FinancePlugin\Models\Plan;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Environment;
use Divido\MerchantSDK\Handlers\ApiRequestOptions;

class PlansService
{
    /**
     * @var integer
     * Duration (in milliseconds) before next plan request
     */
    const REFRESH_RATE = 7200; 

    public static function updatePlans():array
    {
        $recent_plans = self::getStoredPlans();
        if (empty($recent_plans)) {
            $apiKey = Helper::getApiKey();
            if (!empty($apiKey)) {
                $plans = self::getPlansFromSDK($apiKey);
                self::storePlans($plans);
                return $plans;
            }else return [];
        }else return $recent_plans;
    }

    public static function getStoredPlans(int $since = self::REFRESH_RATE):array
    {
        $now = time();
        $recent_plans = Shopware()->Db()->query('SELECT * FROM `s_plans` WHERE `updated_on` > ?', [$now - $since]);
        if(!($recent_plans)) return [];
        else{
            $return = [];
            foreach($recent_plans as $plan){
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

    public static function getPlansFromSDK(string $apiKey):array
    {
        $sdk = new Client(
            $apiKey,
            Helper::getEnvironment($apiKey)
        );
        $requestOptions = (new ApiRequestOptions());

        // Retrieve all finance plans for the merchant.
        $plans = $sdk->getAllPlans($requestOptions);

        $plans = $plans->getResources();
        foreach($plans as $plan){
            $planObj = new Plan;
            $planObj->setId($plan->id);
            $planObj->setName($plan->description);
            $planObj->setDescription($plan->description);
            $return[] = $planObj;
        }

        return $return;
    }

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
            Shopware()->Db()->query("TRUNCATE TABLE `s_plans`");
            $sql = 'INSERT INTO `s_plans` (`id`, `name`, `description`, `updated_on`) VALUES' . implode(",", $inserts);
            Shopware()->Db()->query($sql, $values);
        }
    }
}