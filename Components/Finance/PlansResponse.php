<?php

namespace FinancePlugin\Components\Finance;

class PlansResponse
{
    public $plans;

    public $error;

    public $errorCode;

    public $errorMessage;

    public function __construct($plans=[], $error=false, $errorMessage='', $errorCode=null){
        $this->plans = $plans;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }
    
}