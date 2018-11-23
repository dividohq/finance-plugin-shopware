<?php

namespace FinancePlugin\Models;

use FinancePlugin\Components\Finance\Helper;

class Request
{
    private $countryId = 'GB';
    private $currencyId = 'GBP';
    private $languageId = 'en';
    private $financePlanId = '';
    private $merchantChannelId = '';
    private $applicants = [];
    private $orderItems = [];
    private $depositAmount = 0;
    private $depositPercentage = 0;
    private $finalisationRequired = false;
    private $merchantReference = '';
    private $urls = [
        "merchant_redirect_url" => "",
        "merchant_checkout_url" => "",
        "merchant_response_url" => ""
    ];
    private $metaData = [];

    public function getCountryId():string
    {
        return $this->countryId;
    }

    public function setCountryId($countryId):void
    {
        $this->countryId = $countryId;
    }

    public function getCurrencyId():string
    {
        return $this->currencyId;
    }
    public function setCurrencyId($currencyId):void
    {
        $this->currencyId = $currencyId;
    }

    public function getLanguageId():string
    {
        return $this->languageId;
    }
    public function setLanguageId($languageId):void
    {
        $this->languageId = $languageId;
    }

    public function setFinancePlanId($financePlanId):void
    {
        $this->financePlanId = $financePlanId;
    }
    public function getFinancePlanId():string
    {
        return $this->financePlanId;
    }

    public function setMerchantChannelId($merchantChannelId):void
    {
        $this->merchantChannelId = $merchantChannelId;
    }
    public function getMerchantChannelId():string
    {
        return $this->merchantChannelId;
    }

    public function setApplicants($applicants):void
    {
        $this->applicants = $applicants;
    }
    public function addApplicant($applicant):void
    {
        $this->applicants[] = $applicant;
    }
    public function getApplicants():array
    {
        return $this->applicants;
    }

    public function setOrderItems($orderItems):void
    {
        $this->orderItems = $orderItems;
    }
    public function getOrderItems():array
    {
        return $this->orderItems;
    }

    public function setDepositAmount($depositAmount):void
    {
        $this->depositAmount = $depositAmount;
    }
    public function getDepositAmount():int
    {
        return $this->depositAmount;
    }

    public function setDepositPercentage($depositPercentage):void
    {
        $this->depositPercentage = $depositPercentage;
    }
    public function getDepositPercentage():int
    {
        return $this->depositPercentage;
    }
    
    public function setFinalisationRequired($finalisationRequired):void
    {
        $this->finalisationRequired = $finalisationRequired;
    }
    public function getFinalisationRequired()
    {
        return $this->finalisationRequired;
    }
    
    public function setMerchantReference($merchantReference):void
    {
        $this->merchantReference = $merchantReference;
    }
    public function getMerchantReference():string
    {
        return $this->merchantReference;
    }

    public function setUrls($urls):void
    {
        $this->urls = $urls;
    }
    public function getUrls():array
    {
        return $this->urls;
    }

    public function setMetadata($metaData):void
    {
        $this->metaData = $metaData;
    }
    public function getMetaData():array
    {
        return $this->metaData;
    }
}