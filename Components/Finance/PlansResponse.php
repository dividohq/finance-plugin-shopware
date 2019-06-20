<?php
/**
 * File for PlansResponse class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

/**
 * Class used to break the merchant SDK plans responses up into a more
 * readable format
 */
class PlansResponse
{
    public $plans;

    public $error;

    public $errorCode;

    public $errorMessage;

    /**
     * Plans Response Constructor
     *
     * @param array   $plans        List of plans
     * @param boolean $error        Boolean true if response is error
     * @param string  $errorMessage Error message if error response
     * @param int     $errorCode    Error Code is error response
     */
    public function __construct(
        $plans=[],
        $error=false,
        $errorMessage='',
        $errorCode=null
    ) {
        $this->plans = $plans;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

}