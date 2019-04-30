<?php

namespace FinancePlugin\Components\Finance;

class EnvironmentResponse
{
    public $environment;

    public $error;

    public $errorCode;

    public $errorMessage;

    public function __construct($environment="divido", $error=false, $errorMessage='', $errorCode=null) {
        $this->environment = $environment;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public function _toString(){
        $obj = [
            'environment'  => $this->environment,
            'error'        => $this->error,
            'errorCode'    => $this->errorCode,
            'errorMessage' => $this->errorMessage
        ];
        return json_encode($obj);
    }

}