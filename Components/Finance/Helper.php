<?php

namespace FinancePlugin\Components\Finance;

class Helper
{
    /**
     * Log wrapper which checks to see if Debug is on in the
     * plugin config
     *
     * @param mixed  $msg  A string with the message to debug.
     * @param string $type PHP error level error warning.
     *
     * @return void
     */
    public static function debug($msg, $type = false)
    {
        $debug = self::getDebug();

        if (! $debug) {
            return false;
        }
        self::log($msg, $type);
    }

    /**
     * Log Helper
     *
     * @param mixed  $msg  String to be passed
     * @param string $type Type to be used
     *
     * @return void
     */
    public static function log($msg, $type)
    {
        switch ($type) {
            case 'warning':
                Shopware()->PluginLogger()->warning("Warning: ". $msg);
                break;
            
            case 'info':
                Shopware()->PluginLogger()->info("Info: " . $msg);
                break;

            case 'error':
                Shopware()->PluginLogger()->error("Error: " . $msg);
                break;
            
            default:
                Shopware()->PluginLogger()->info("Default info: " . $msg);
                break;
        }
        return;
    }

    /**
     * Helper to grab the plugin configuration
     *
     * @return array
     */
    public static function getConfig()
    {

        $config = Shopware()
            ->Container()
            ->get('shopware.plugin.cached_config_reader')
            ->getByPluginName('LendingPlatform');

        return $config;
    }

    /**
     * Helper to grab the conf by key
     *
     * @return string
     */
    public static function getConfByKey($key)
    {
        $config = self::getConfig();
        return $config[$key];
    }

    /**
     * Helper to grab api key
     *
     * @return string
     */
    public static function getApiKey()
    {
        $config = self::getConfig();
        return $config['Api Key'];
    }

    /**
     * Helper to grab debug status
     *
     * @return bool
     */
    public static function getDebug()
    {
        $config = self::getConfig();
        return $config['Debug'];
    }

    /**
     * Helper to grab checkout title
     *
     * @return string
     */
    public static function getTitle()
    {
        $config = self::getConfig();
        return $config['Checkout Title'];
    }

    /**
     * Helper to grab description
     *
     * @return string
     */
    public static function getDescription()
    {
        $config = self::getConfig();
        return $config['Checkout Description'];
    }
    /**
     * Helper to grab shared secret value
     *
     * @return string
     */
    public static function getSharedSecret()
    {
        $config = self::getConfig();
        return $config['Shared Secret'];
    }
    /**
     * Helper to grab cart threshold value
     *
     * @return int
     */
    public static function getCartThreshold()
    {
        $config = self::getConfig();
        return $config['Cart Threshold'];
    }

    /**
     * Helper Function to transform shopware address array to plugin format
     *
     * @param array $shopwareAddressArray shopware address
     *
     * @return array
     */
    private function formatShopwareAddress($shopwareAddressArray)
    {
        self::debug('Add array:'.serialize($shopwareAddressArray), 'info');

        $addressText = $shopwareAddressArray['buildingNumber'] .' '.
         $shopwareAddressArray['street'] . ' ' .
         $shopwareAddressArray['city'] . ' ' .
         $shopwareAddressArray['zipcode'];
         
        $addressArray = array();
        $addressArray['postcode'] = $shopwareAddressArray['zipcode'];
        $addressArray['street'] = $shopwareAddressArray['street'];
        $addressArray['flat'] = $shopwareAddressArray['flat'];
        $addressArray['buildingNumber']
            = $shopwareAddressArray['buildingNumber'];
        $addressArray['buildingName'] = $shopwareAddressArray['buildingName'];
        $addressArray['town'] = $shopwareAddressArray['city'];
        $addressArray['text'] = $addressText;

        return $addressArray;
    }
    
    /**
     * Create order detail for plugin credit request
     *
     * @param array $shopwareBasketArray The array from shopwares basket
     *
     * @return void
     */
    public function getOrderProducts($shopwareBasketArray)
    {
        $productsArray = array();
        //Add tax
        $i=0;
            
        foreach ($shopwareBasketArray['content'] as $id => $product) {
            $productsArray[$i]['name']     = $product['articlename'];
            $productsArray[$i]['quantity'] = $product['quantity'];
            $productsArray[$i]['price']    = $product['price'];
            if ($product['modus'] == '0') {
                $productsArray[$i]['plans']
                = $product['additional_details']['attributes']['core']->get('finance_plans');
            }
            $i++;
        }
        $productsArray[$i]['name'] = 'Shipping';
        $productsArray[$i]['quantity'] = '1';
        $productsArray[$i]['price'] = $shopwareBasketArray['sShippingcosts'];
        
        return $productsArray;
    }

    /** 
     * Work out the total deposit amount from  the percentage and round it
     *
     * @param float $total   total of the order
     * @param float $deposit deposit amount
     *
     * @return float
     */
    public function getDepositAmount($total, $deposit)
    {
        if(empty($deposit)) return 0;
        
        $depositPercentage = $deposit / 100;
        return round($depositPercentage * $total, 2);
    }

    public function getBasketPlans($products){
        $basket_plans = [];
        foreach($products as $product){
            if(isset($product['plans'])){
                $product_plans = explode("|",$product['plans']);
                if(empty($basket_plans)){
                    foreach($product_plans as $plan){
                        if(!empty($plan)) $basket_plans[] = $plan;
                    }
                }else{
                    if(!empty($product_plans)){
                        foreach($basket_plans as $k=>$listed){
                            if(!in_array($listed,$product_plans)){
                                unset($basket_plans[$k]);
                            }
                        }
                    }
                }
            }
        }
        return $basket_plans;
    }

        /**
     * Create customer details for credit request
     *
     * @return Array
     */
    public static function getFormattedCustomerDetails($user):array
    {
        self::debug('Formatting Customer Details');

        $billing = $user['billingaddress'];
        $shipping = $user['shippingaddress'];

        $billingAddress = self::formatShopwareAddress($billing);
        $shippingAddress = self::formatShopwareAddress($shipping);
        $country = $user['additional']['country']['countryiso'];

        $customerArray = array();
        $customerArray['country'] = $country;
        $customerArray['customer'] = array(
            'firstName' => $billing['firstname'],
            'lastName' => $billing['lastname'],
            'email' => $user['additional']['user']['email'],
            'address' => $billingAddress,
            'shippingAddress' => $shippingAddress,
        );
        self::debug('CustomerArray:' . serialize($customerArray), 'info');

        return $customerArray;
    }

    /**
     * Create HMAC SIGNATURE
     *
     * @param string $payload      The payload for the signature
     * @param string $sharedSecret The secret stored on the portal
     *
     * @return $signature
     */
    public static function createSignature($payload, $sharedSecret)
    {
        $signature = base64_encode(
            hash_hmac('sha256', $payload, $sharedSecret, true)
        );
        return $signature;
    }

    public function hmacSign(){
        //Not working
        if (isset($_SERVER['HTTP_RAW_POST_DATA']) 
            && $_SERVER['HTTP_RAW_POST_DATA']) {
            self::debug('Raw Data :','info');

            $data = file_get_contents($_SERVER['HTTP_RAW_POST_DATA']);
        } else {
            self::debug('PHP input:','info');
            $data = file_get_contents('php://input');
        }

        self::debug('Shared Secret:'.$this->getSharedSecret(),'info');

        $sharedSecret = self::getSharedSecret();
        if(!empty($sharedSecret))
        {
            $callback_sign = $_SERVER['HTTP_X_DIVIDO_HMAC_SHA256']; /* TODO: Can this change?  */

            self::debug('Callback Sign: '.$callback_sign , 'info');
            print_r($callback_sign);
            self::debug('Callback DATA: '.$data,'info');
            print_r($data);

            $sign = self::createSignature($data, $sharedSecret);

            self::debug('Created Signature: '.$sign,'info');

            if ( $callback_sign !== $sign ) {
                self::debug('Hash error','error');
                return false;
            }
        }
        return true;
    }

    public function getEnvironment($environment){
        if(isset(Divido\MerchantSDK\Environment::$$environment)){
            $environment == Divido\MerchantSDK\Environment::$$environment;
        }else return false;
    }

}