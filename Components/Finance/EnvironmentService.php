<?php
/**
 * File containing the EnvironmentService class
 *
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

use FinancePlugin\Models\Environment;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Handlers\ApiRequestOptions;
use Divido\MerchantSDK\Exceptions\MerchantApiBadResponseException;

/**
 * Helper service to maintain finance plans
 *
 * @category CategoryName
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class EnvironmentService
{
    const PLUGIN_ID = 1;
    /**
     * Retrieve client environment from SDK by API Key
     *
     * @param string $apiKey The API key
     *
     * @return array
     */
    public static function getEnvironmentResponse(string $apiKey):EnvironmentResponse
    {
        $environment = Helper::getEnvironment($apiKey);

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

        $requestOptions = (new ApiRequestOptions());
        // Retrieve all finance plans for the merchant.
        try{
            $response = $sdk->platformEnvironments()->getPlatformEnvironment();
            $finance_env = $response->getBody()->getContents();
            $response = new EnvironmentResponse($finance_env);
            return $response;
        }catch(MerchantApiBadResponseException $e){
            $errorMessage = SDKErrorHandler::getMessage($e);
            $response = new EnvironmentResponse("{}", true, $e->getCode(), $errorMessage);
            return $response;
        }
    }

    public static function storeEnvironment(Environment $environment) {
        $now = time();
        $environment->setUpdatedOn($now);

        $values = [
            $environment->getId(),
            $environment->getPluginId(),
            $environment->getEnvironment(),
            $environment->getUpdatedOn()
        ];

        //self::clearEnvironmentsByPluginId(self::PLUGIN_ID);
        $sql = 'INSERT INTO `s_environments`
                    (`id`, `plugin_id`, `environment`, `updated_on`)
                VALUES
                    (?, ?, ?, ?)';

        Shopware()->Db()->query($sql, $values);
    }

    public static function retrieveEnvironmentFromDb(int $id) {
        $get_session_query = $connection->createQueryBuilder();
        $session_sql
            = "SELECT * FROM `s_environments` WHERE `id`= :id LIMIT 1";
        $session = $connection->fetchAll($session_sql, [':id' => $id]);
        if (isset($session[0])) {
            $environment = new Environment;
            $environment->setId($id);
            $environment->setPluginId($session[0]['plugin_id']);
            $environment->setEnvironment($session[0]['environment']);
            $environment->setUpdatedOn($session[0]['updated_on']);
            return $environment;
        } else return false;
    }

    public static function retrieveEnvironmentFromDbByPluginId(int $pluginId) {
        $get_session_query = $connection->createQueryBuilder();
        $session_sql
            = "SELECT * FROM `s_environments` WHERE `plugin_id`= :plugin_id ORDER BY `updated_on` DESC LIMIT 1";
        $session = $connection->fetchAll($session_sql, [':plugin_id' => $pluginId]);
        if (isset($session[0])) {
            $environment = new Environment;
            $environment->setId($id);
            $environment->setPluginId($session[0]['plugin_id']);
            $environment->setEnvironment($session[0]['environment']);
            $environment->setUpdatedOn($session[0]['updated_on']);
            return $environment;
        } else return false;
    }

    public static function constructEnvironmentFromResponse(EnvironmentResponse $response) {
        if ($response->error === false) {
            $response_array = json_decode($response->environment, true);
            $environment = new Environment;
            $environment->setPluginId(self::PLUGIN_ID);
            $environment->setEnvironment($response_array['data']['environment']);
            $environment->setUpdatedOn(time());
            return $environment;
        } else return false;
    }

    public static function clearEnvironmentsByPluginId($pluginId) {
        if (Shopware()->Db()->query("TRUNCATE TABLE `s_environments`")) {
            return true;
        } else return false;
    }
}