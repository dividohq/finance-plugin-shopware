<?php

namespace FinancePlugin\Components\Finance;

class ActivateResponse
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
            $this->amount = $responseObj->id;
            $this->status = $responseObj->id;
            $this->reference = $responseObj->id;
            $this->data = $responseObj->id;
            $this->comment = $responseObj->id;
            $this->created_at = $responseObj->id;
            $this->updated_at = $responseObj->id;

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