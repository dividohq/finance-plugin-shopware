<?php

/**
 * File for Webhook Service class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

/**
 * Service that handles webhooks
 *
 */
class WebhookService
{
    const
        PAYMENTSTATUSPAID = 12,
        PAYMENTSTATUSOPEN = 17,
        PAYMENTREVIEWNEEDED = 21,
        PAYMENTCANCELLED = 35,
        PAYMENTSTATUSAWAITINGACTIVATION = 40,
        PAYMENTSTATUSREFUNDED = 41,

        STATUS_PROPOSAL = 'PROPOSAL',
        STATUS_ACCEPTED = 'ACCEPTED',
        STATUS_ACTION_LENDER = 'ACTION-LENDER',
        STATUS_CANCELLED = 'CANCELLED',
        STATUS_COMPLETED = 'COMPLETED',
        STATUS_DEFERRED = 'DEFERRED',
        STATUS_DECLINED = 'DECLINED',
        STATUS_DEPOSIT_PAID = 'DEPOSIT-PAID',
        STATUS_FULFILLED = 'FULFILLED',
        STATUS_REFERRED = 'REFERRED',
        STATUS_SIGNED = 'SIGNED',
        STATUS_READY = 'READY',
        STATUS_ACTIVATED = 'ACTIVATED',
        STATUS_REFUNDED = 'REFUNDED',
        STATUS_AWAITING_CANCELLATION = 'AWAITING-CANCELLATION',
        STATUS_AWAITING_ACTIVATION = 'AWAITING-ACTIVATION';


    /**
     * Order History Mesaages
     *
     * @category CategoryName
     *
     * @var array
     */
    public $historyMessages = array (
        self::STATUS_ACCEPTED => 'Credit request accepted',
        self::STATUS_ACTION_LENDER => 'Lender notified',
        self::STATUS_CANCELLED => 'Application cancelled',
        self::STATUS_COMPLETED => 'Application completed',
        self::STATUS_DEFERRED => 'Application deferred by Underwriter,
         waiting for new status',
        self::STATUS_DECLINED => 'Applicaiton declined by Underwriter',
        self::STATUS_DEPOSIT_PAID => 'Deposit paid by customer',
        self::STATUS_FULFILLED => 'Credit request fulfilled',
        self::STATUS_REFERRED => 'Credit request referred by Underwriter,
         waiting for new status',
        self::STATUS_SIGNED => 'Customer have signed all contracts',
        self::STATUS_READY => 'Order ready to Ship'
    );

    /**
     * Generate response information from the
     * webhook status
     *
     * @param string $status Received Status
     *
     * @return array
     */
    public function getStatusInfo(string $status)
    {

        $statusInfo = array(
            'message'        => '',
            'session_status' => null,
            'order_status'   => null,
            'code'           => 200,
            'status'         => 'ok'
        );

        switch ($status) {
        case self::STATUS_PROPOSAL:
            Helper::debug('Webhook: Proposal', 'info');
            $statusInfo['message'] = 'Proposal Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_ACCEPTED:
            Helper::debug('Webhook: Accepted', 'info');
            $statusInfo['message'] = 'Accepted Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_SIGNED:
            Helper::debug('Webhook: Signed', 'info');
            $statusInfo['message'] = 'Signed Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSPAID;
            break;

        case self::STATUS_DECLINED:
            Helper::debug('Webhook: Declined', 'info');
            $statusInfo['message'] = 'Declined Hook Success';
            $statusInfo['order_status'] = self::PAYMENTREVIEWNEEDED;
            $statusInfo['session_status'] = self::PAYMENTREVIEWNEEDED;
            break;

        case self::STATUS_CANCELLED:
            Helper::debug('Webhook: Cancelled', 'info');
            $statusInfo['message'] = 'Cancelled Hook Success';
            $statusInfo['order_status'] = self::PAYMENTCANCELLED;
            $statusInfo['session_status'] = self::PAYMENTCANCELLED;
            break;

        case self::STATUS_AWAITING_CANCELLATION:
            Helper::debug('Webhook: Awaiting Cancellation', 'info');
            $statusInfo['message'] = 'Awaiting Cancellation Hook Success';
            $statusInfo['order_status'] = self::PAYMENTCANCELLED;
            $statusInfo['session_status'] = self::PAYMENTCANCELLED;
            break;

        case self::STATUS_DEPOSIT_PAID:
            Helper::debug('Webhook: Deposit Paid', 'info');
            $statusInfo['message'] = 'Deposit Paid Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSOPEN;
            break;

        case self::STATUS_REFUNDED:
            Helper::debug('Webhook: Deposit Refunded', 'info');
            $statusInfo['message'] = 'Deposit Refunded Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSREFUNDED;
            break;

        case self::STATUS_AWAITING_ACTIVATION:
            Helper::debug('Webhook: Awating Activation', 'info');
            $statusInfo['message'] = 'Awaiting Activation Hook Success';
            $statusInfo['session_status'] = self::PAYMENTSTATUSAWAITINGACTIVATION;
            break;

        case self::STATUS_ACTION_LENDER:
            Helper::debug('Webhook: Deposit Paid', 'info');
            break;

        case self::STATUS_COMPLETED:
            $statusInfo['message'] = 'Completed';
            Helper::debug('Webhook: Completed', 'info');
            break;

        case self::STATUS_DEFERRED:
            $statusInfo['message'] = 'Deferred Success';
            Helper::debug('Webhook: STATUS_DEFERRED', 'info');
            break;

        case self::STATUS_FULFILLED:
            $statusInfo['message'] = 'STATUS_FULFILLED Success';
            Helper::debug('Webhook: STATUS_FULFILLED', 'info');
            break;

        case self::STATUS_REFERRED:
            $statusInfo['message'] = 'Order Referred Success';
            Helper::debug('Webhook: Referred', 'info');
            break;

        case self::STATUS_READY:
            $statusInfo['message'] = 'Status Ready';
            Helper::debug('Webhook: Ready', 'info');
            break;

        case self::STATUS_REFUNDED:
            $statusInfo['message'] = 'Status Ready';
            Helper::debug('Webhook: Ready', 'info');
            break;

        default:
            $statusInfo['message'] = 'Empty Hook';
            $statusInfo['code'] = 400;
            $statusInfo['status'] = 'error';
            Helper::debug('Webhook: Empty webook', 'warning');
            break;
        }

        return $statusInfo;
    }


    /**
     * Webhook Helper
     *
     * @param \Enlight_Controller_Request_Request $request Data received
     *
     * @return WebhookResponse
     */
    public function createWebhookResponse(
        \Enlight_Controller_Request_Request $request
    ) {

        $data = json_decode($request->getRawBody());

        $webhookResponse = new WebhookResponse();

        $webhookResponse->event       = $data->event;
        $webhookResponse->status      = $data->status;
        $webhookResponse->name        = $data->name;
        $webhookResponse->email       = $data->email;
        $webhookResponse->proposal    = $data->proposal;
        $webhookResponse->application = $data->application;
        $webhookResponse->signature   = $data->metadata->signature;
        $webhookResponse->token       = $data->metadata->token;
        $webhookResponse->amount      = $data->metadata->amount;

        return $webhookResponse;
    }

}