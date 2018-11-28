<?php

namespace FinancePlugin\Components\Finance;

class PlansResponse
{
    public $plans;

    public $error;

    public $errorMessage;

    public function _construct($plans=[], $error=false, $errorMessage=''){
        $this->plans = $plans;
        $this->error = $error;
        $this->errorMessage = $errorMessage;
    }
    
}