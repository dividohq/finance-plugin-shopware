<?php

/**
 * File for the PaymentResponse class
 * 
 * PHP version 7.1
 */

namespace FinancePlugin\Components\Finance;

/**
 * Object for the Payment Response
 */
class PaymentResponse
{
    /**
     * Transaction ID
     * 
     * @var int
     */
    public $transactionId;

    /**
     * Received Token
     * 
     * @var string
     */
    public $token;

    /**
     * Received Status
     * 
     * @var string
     */
    public $status;
}