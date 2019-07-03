<?php
/**
 * Payment Service - Webhook Response
 *
 * PHP version 5.5
 */
namespace dividoFinancePlugin\Components\Finance;

/**
 * Webhook Response class
 *
 * @category CategoryName
 * @package  dividoFinancePlugin
 * @since    File available since Release 1.0.0
 */
class WebhookResponse
{
    /**
     * Description of the event - application-status-update
     *
     * @var string
     */
    public $event;

    /**
     * Status Code returned
     *
     * @var string
     */
    public $status;

    /**
     * Customers Name
     *
     * @var string
     */
    public $name;

    /**
     * Customers last name
     *
     * @var string
     */
    public $lastname;

    /**
     * Customer email address
     *
     * @var string Customer email address
     */
    public $email;

    /**
     * Unique Identifier
     *
     * @var string
     */
    public $proposal;

    /**
     * Unique Identifiers
     *
     * @var string
     */
    public $application;

    /**
     *  Unique Basket Signatute
     *
     * @var string
     */
    public $signature;

    /**
     * The token
     *
     * @var string
     */
    public $token;

    /**
     * The booking Id
     *
     * @var string
     */
    public $bookingId;
}
