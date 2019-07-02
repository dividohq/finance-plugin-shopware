<?php
/**
 * File for SDK Handler class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

use Divido\MerchantSDK\Exceptions\MerchantApiBadResponseException;

 /**
  * SDKErrorHandler class to catch and better handle
  * errors from the SDK
  */
class SDKErrorHandler
{

    const UNAUTHORISED = 401001;

    /**
     * Processes the code received from the MerchantApiBadResponseException
     * class and returns a better response where possible
     *
     * @param MerchantApiBadResponseException $e Error thrown by SDK
     *
     * @return string
     */
    public static function getMessage(MerchantApiBadResponseException $e)
    {
        switch ($e->getCode()) {
        case self::UNAUTHORISED:
            $error_message
                = "The API Key seems to bee invalid. Please go to the plugins
                config page and double check the information is correct.";
            break;
        default:
            $error_message = $e->getMessage();
            break;
        }
        return $error_message;
    }
}