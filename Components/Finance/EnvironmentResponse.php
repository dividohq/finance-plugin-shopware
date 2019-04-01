<?php

namespace FinancePlugin\Components\Finance;

class EnvironmentResponse
{
    public $environment;

    public $error;

    public $errorCode;

    public $errorMessage;

    public function __construct($environment="{}", $error=false, $errorMessage='', $errorCode=null) {
        $this->environment = $environment;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }
    
}