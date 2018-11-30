<?php
/**
 * File for Request model
 * 
 * PHP version 7.2
 */
namespace FinancePlugin\Models;

use FinancePlugin\Components\Finance\Helper;

/**
 * Application Request Model
 */
class Request
{
    private $_countryId = 'GB';
    private $_currencyId = 'GBP';
    private $_languageId = 'en';
    private $_financePlanId = '';
    private $_merchantChannelId = '';
    private $_applicants = [];
    private $_orderItems = [];
    private $_depositAmount = 0;
    private $_depositPercentage = 0;
    private $_finalisationRequired = false;
    private $_merchantReference = '';
    private $_urls = [
        "merchant_redirect_url" => "",
        "merchant_checkout_url" => "",
        "merchant_response_url" => ""
    ];
    private $_metaData = [];

    /**
     * Get Country ID
     *
     * @return string
     */
    public function getCountryId():string
    {
        return $this->_countryId;
    }

    /**
     * Set Country ID
     *
     * @param string $countryId New Country ID
     * 
     * @return void
     */
    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;
    }

    /**
     * Get Currency ID
     *
     * @return string
     */
    public function getCurrencyId():string
    {
        return $this->_currencyId;
    }

    /**
     * Set Currenct ID
     *
     * @param string $currencyId New currency ID
     * 
     * @return void
     */
    public function setCurrencyId($currencyId)
    {
        $this->_currencyId = $currencyId;
    }

    /**
     * Get the Language ID
     *
     * @return string
     */
    public function getLanguageId():string
    {
        return $this->_languageId;
    }

    /**
     * Set the Language ID
     *
     * @param string $languageId New Language ID
     * 
     * @return void
     */
    public function setLanguageId($languageId)
    {
        $this->_languageId = $languageId;
    }

    /**
     * Set the Finance plan ID
     *
     * @param string $financePlanId New Finance Plan ID
     * 
     * @return void
     */
    public function setFinancePlanId($financePlanId)
    {
        $this->_financePlanId = $financePlanId;
    }

    /**
     * Get Finance Plan ID
     *
     * @return string Finance Plan ID
     */
    public function getFinancePlanId():string
    {
        return $this->_financePlanId;
    }

    /**
     * Set the Merchant Channel ID
     *
     * @param string $merchantChannelId New Merchant Channel ID
     * 
     * @return void
     */
    public function setMerchantChannelId($merchantChannelId)
    {
        $this->_merchantChannelId = $merchantChannelId;
    }

    /**
     * Get the Merchant Channel ID
     *
     * @return string
     */
    public function getMerchantChannelId():string
    {
        return $this->_merchantChannelId;
    }

    /**
     * Set the Applicants
     *
     * @param Array $applicants Array of Applicants
     * 
     * @return void
     */
    public function setApplicants(array $applicants)
    {
        $this->_applicants = $applicants;
    }

    /**
     * Add an applicant
     *
     * @param Array $applicant Applicant Array
     * 
     * @return void
     */
    public function addApplicant(array $applicant)
    {
        $this->_applicants[] = $applicant;
    }

    /**
     * Get Applicants Array
     *
     * @return array
     */
    public function getApplicants():array
    {
        return $this->_applicants;
    }

    /**
     * Set Order Items array
     *
     * @param array $orderItems New Order Items array
     * 
     * @return void
     */
    public function setOrderItems(array $orderItems)
    {
        $this->_orderItems = $orderItems;
    }

    /**
     * Get the Order Items
     *
     * @return array
     */
    public function getOrderItems():array
    {
        return $this->_orderItems;
    }

    /**
     * Set Deposit Amount (in pence)
     *
     * @param integer $depositAmount New deposit amount (in pence)
     * 
     * @return void
     */
    public function setDepositAmount($depositAmount)
    {
        $this->_depositAmount = $depositAmount;
    }

    /**
     * Get the Deposit Amount
     *
     * @return integer
     */
    public function getDepositAmount():int
    {
        return $this->_depositAmount;
    }

    /**
     * Set decimal value of deposit
     *
     * @param decimal $depositPercentage New deposit decimal
     * 
     * @return void
     */
    public function setDepositPercentage($depositPercentage)
    {
        $this->_depositPercentage = $depositPercentage;
    }

    /**
     * Get the Deposit Percentage
     *
     * @return decimal
     */
    public function getDepositPercentage():decimal
    {
        return $this->_depositPercentage;
    }
    
    /**
     * Set whether finalisation is required
     *
     * @param boolean $finalisationRequired Finalisation Required?
     * 
     * @return void
     */
    public function setFinalisationRequired($finalisationRequired)
    {
        $this->_finalisationRequired = $finalisationRequired;
    }

    /**
     * Get whether Finalisation is required
     *
     * @return boolean
     */
    public function getFinalisationRequired()
    {
        return $this->_finalisationRequired;
    }
    
    /**
     * Set the unique Merchant Reference
     *
     * @param string $merchantReference New Merchant Reference
     * 
     * @return void
     */
    public function setMerchantReference($merchantReference)
    {
        $this->_merchantReference = $merchantReference;
    }

    /**
     * Get the Merchant Reference
     *
     * @return string Merchant reference
     */
    public function getMerchantReference():string
    {
        return $this->_merchantReference;
    }

    /**
     * Set URL's
     *
     * @param array $urls New URL's
     * 
     * @return void
     */
    public function setUrls(array $urls)
    {
        $this->_urls = $urls;
    }

    /**
     * Get URL's
     *
     * @return array
     */
    public function getUrls():array
    {
        return $this->_urls;
    }

    /**
     * Set MetaData
     *
     * @param array $metaData New MetaData array
     * 
     * @return void
     */
    public function setMetadata(array $metaData)
    {
        $this->_metaData = $metaData;
    }

    /**
     * Get MetaData array
     *
     * @return array
     */
    public function getMetaData():array
    {
        return $this->_metaData;
    }
}