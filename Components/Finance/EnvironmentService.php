<?php
/**
 * File containing the EnvironmentService class
 *
 * PHP version 7.1
 */

namespace dividoFinancePlugin\Components\Finance;

use dividoFinancePlugin\Models\Environment;
use Divido\MerchantSDK\Client;
use Divido\MerchantSDK\Handlers\ApiRequestOptions;
use Divido\MerchantSDK\Exceptions\MerchantApiBadResponseException;

/**
 * Helper service to maintain finance plans
 *
 * @category CategoryName
 * @package  dividoFinancePlugin
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

        if (!$environment) {
            return new EnvironmentResponse("", true, "Unexpected API Key");
        }

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
        try {
            $response = $sdk->platformEnvironments()->getPlatformEnvironment();
            $responseStr = $response->getBody()->getContents();
            $responseJson = json_decode($responseStr);
            if (isset($responseJson->data->environment)) {
                $responseObj = new EnvironmentResponse(
                    $responseJson->data->environment
                );
            } elseif (isset($responseJson->error)) {
                $responseObj = new EnvironmentResponse(
                    "",
                    true,
                    $responseJson->message,
                    $responseJson->code
                );
            }
            Helper::log("Response: ".$responseObj->toString(), 'info');
            return $responseObj;
        } catch(MerchantApiBadResponseException $e) {
            $errorMessage = SDKErrorHandler::getMessage($e);
            $responseObj = new EnvironmentResponse(
                "",
                true,
                $errorMessage,
                $e->getCode()
            );
            return $responseObj;
        }
    }

    /**
     * Undocumented function
     *
     * @param Environment $environment The merchant environment
     *
     * @return void
     */
    public static function storeEnvironment(Environment $environment)
    {
        $now = time();
        $environment->setUpdatedOn($now);

        $values = [
            $environment->getPluginId(),
            $environment->getEnvironment(),
            $environment->getUpdatedOn()
        ];

        self::clearEnvironmentsByPluginId(self::PLUGIN_ID);
        $sql = 'INSERT INTO `s_plugin_dividoFinancePlugin_environments`
                    (`plugin_id`, `environment`, `updated_on`)
                VALUES
                    (?, ?, ?)';

        $id = Shopware()->Db()->query($sql, $values);

        $environment->setId($id);
    }

    /**
     * Retrieve the merchant environment cached in the DB by ID
     *
     * @param integer $id Environment ID
     *
     * @return dividoFinancePlugin\Components\Finance\Environment | false
     */
    public static function retrieveEnvironmentFromDb(int $id)
    {
        $session_sql
            = "SELECT * FROM `s_plugin_dividoFinancePlugin_environments` WHERE `id`= :id LIMIT 1";
        $sessions = Shopware()->Db()->query($session_sql, [':id' => $id]);
        foreach ($sessions as $session) {
            $environment = new Environment;
            $environment->setId($id);
            $environment->setPluginId($session['plugin_id']);
            $environment->setEnvironment($session['environment']);
            $environment->setUpdatedOn($session['updated_on']);
            return $environment;
        }
        return false;
    }

    /**
     * Retrieve the environment from the DB by Plugin ID
     *
     * @param integer $pluginId Plugin ID
     *
     * @return dividoFinancePlugin\Components\Finance\Environment | false
     */
    public static function retrieveEnvironmentFromDbByPluginId(int $pluginId)
    {
        $session_sql = "
            SELECT *
            FROM `s_plugin_dividoFinancePlugin_environments`
            WHERE `plugin_id`= :plugin_id
            ORDER BY `updated_on` DESC
            LIMIT 1
        ";
        $sessions = Shopware()->Db()->query(
            $session_sql,
            [':plugin_id' => $pluginId]
        );
        foreach ($sessions as $session) {
            $environment = new Environment;
            $environment->setId($id);
            $environment->setPluginId($session['plugin_id']);
            $environment->setEnvironment($session['environment']);
            $environment->setUpdatedOn($session['updated_on']);
            return $environment;
        }
        return false;
    }

    /**
     * Construct environment based on response
     *
     * @param EnvironmentResponse $response An environment response object
     *
     * @return dividoFinancePlugin\Components\Finance\Environment | false
     */
    public static function constructEnvironmentFromResponse(
        EnvironmentResponse $response
    ) {
        if (false === $response->error) {
            $environment = new Environment;
            $environment->setPluginId(self::PLUGIN_ID);
            $environment->setEnvironment($response->environment);
            $environment->setUpdatedOn(time());
            return $environment;
        } else {
            return false;
        }
    }

    /**
     * Clear environments with the supplied plugin ID
     *
     * @param int $pluginId a plugin ID
     *
     * @return boolean
     */
    public static function clearEnvironmentsByPluginId($pluginId)
    {
        if (Shopware()->Db()->query("TRUNCATE TABLE `s_plugin_dividoFinancePlugin_environments`")) {
            return true;
        } else {
            return false;
        }
    }
}