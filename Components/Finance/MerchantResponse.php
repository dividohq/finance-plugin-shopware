<?php
/**
 * File for Merchant Response class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

/**
 * Class used to break the merchant SDK request responses up into a more
 * readable format
 */
class MerchantResponse
{
    public $id;

    public $amount;

    public $status;

    public $reference;

    public $data;

    public $comment;

    public $created_at;

    public $updated_at;

    public $error;

    public $code;

    public $message;

    /**
     * Merchant response constructor
     *
     * @param Object $responseObj The response object
     */
    public function __construct($responseObj)
    {
        if (isset($responseObj->data->id)) {

            $this->id = $responseObj->data->id;
            $this->amount = $responseObj->data->amount;
            $this->status = $responseObj->data->status;
            $this->reference = $responseObj->data->reference;
            $this->data = $responseObj->data->data;
            $this->comment = $responseObj->data->comment;
            $this->created_at = $responseObj->data->created_at;
            $this->updated_at = $responseObj->data->updated_at;

            $this->error = false;

        } elseif (isset($responseObj->error)) {
            $this->error = $responseObj->error;
            $this->code = $responseObj->code;
            $this->message = $responseObj->message;
        } else {
            $responseObj->error = true;
            $responseObj->message = "Unknown error";
        }
    }

}