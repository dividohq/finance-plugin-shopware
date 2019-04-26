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

    public function __construct($responseObj){
        if(isset($responseObj->id)) {

            $this->id = $responseObj->id;
            $this->amount = $responseObj->amount;
            $this->status = $responseObj->status;
            $this->reference = $responseObj->reference;
            $this->data = $responseObj->data;
            $this->comment = $responseObj->comment;
            $this->created_at = $responseObj->created_at;
            $this->updated_at = $responseObj->updated_at;

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