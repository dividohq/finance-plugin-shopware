<?php
/**
 * File for EnvironmentResponse class
 *
 * PHP version 7.1
 */

namespace dividoFinancePlugin\Components\Finance;

/**
 * Object for the Environment Response
 *
 * @category CategoryName
 * @package  dividoFinancePlugin
 * @since    File available since Release 1.0.0
 */
class EnvironmentResponse
{
    public $environment;

    public $error;

    public $errorCode;

    public $errorMessage;

    /**
     * Constructor function
     *
     * @param string  $environment  Merchant environment
     * @param boolean $error        Whether response is an error
     * @param string  $errorMessage If error response, the message
     * @param int     $errorCode    If error response, the error code
     */
    public function __construct(
        $environment="divido",
        $error=false,
        $errorMessage='',
        $errorCode=null
    ) {
        $this->environment = $environment;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * JSON encode Environment response
     *
     * @return string json encoded response
     */
    public function toString()
    {
        $obj = [
            'environment'  => $this->environment,
            'error'        => $this->error,
            'errorCode'    => $this->errorCode,
            'errorMessage' => $this->errorMessage
        ];
        return json_encode($obj);
    }

}