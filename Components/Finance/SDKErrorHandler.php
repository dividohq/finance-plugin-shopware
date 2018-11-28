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

    public static function getMessage(MerchantApiBadResponseException $e):string{
        switch ($e->getCode()) {
            case self::UNAUTHORISED:
                $error_message
                    = "The API Key was deemed invalid";
                break;
            default:
                $error_message = $e->getMessage();
                break;
        }
        return $error_message;
    }

  }