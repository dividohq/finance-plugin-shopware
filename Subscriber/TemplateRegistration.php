<?php
/**
 * File for TemplateRegistration class
 * 
 * PHP version 7.1
 */

namespace FinancePlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use FinancePlugin\Components\Finance\PaymentService;
use FinancePlugin\Components\Finance\PlansService;
use FinancePlugin\Components\Finance\Helper;

/**
 * Payment Service  Class
 *
 * @category Payment_Gateway
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class TemplateRegistration implements SubscriberInterface
{
    /*
     * @var string
     */
    private $_pluginDirectory; //

    /*
     * @var \Enlight_Template_Manager
     */
    private $_templateManager; //

    /**
     * Constructor function
     * 
     * @param string                    $pluginDirectory The plugin directory
     * @param \Enlight_Template_Manager $templateManager The Template Manager
     */
    public function __construct(
        $pluginDirectory, 
        \Enlight_Template_Manager $templateManager
    ) {
        $this->_pluginDirectory = $pluginDirectory;
        $this->_templateManager = $templateManager;
    }

    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' 
                => 'onPreDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' 
                => 'onPostDispatchSecure',
        ];
    }

    /**
     * Class run before dispatch
     *
     * @param \Enlight_Controller_ActionEventArgs $args Arguments
     * 
     * @return void
     */
    public function onPreDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $args
            ->get('subject')
            ->View()
            ->addTemplateDir($this->_pluginDirectory . '/Resources/views');
        return;
    }

    /**
     * Class run after dispatch
     *
     * @param \Enlight_Controller_ActionEventArgs $args Arguments
     * 
     * @return void
     */
    public function onPostDispatchSecure(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->get('subject');
        $view = $controller->View();
        
        if ($controller->Request()->getActionName() == 'index') {
            $product = $view->sArticle;

            $config = Helper::getConfig();
            
            $show_widget = false;
            if ($config['Show Widget']) {
               
                $min_product_amount 
                    = (isset($config['Widget Minimum'])) 
                    ? $config['Widget Minimum']*100 
                    : 0;
                
                $product_price = filter_var(
                    $product['price'], 
                    FILTER_SANITIZE_NUMBER_INT
                );

                if ($product_price > $min_product_amount) {
                    $apiKey = $config["API Key"];
                    $key = preg_split("/\./", $apiKey);
                    $view->assign('apiKey', $key[0]);
                    
                    $view->assign('plans', implode(",", $plans_ids));

                    $suffix 
                        = ($config['Widget Suffix']) 
                        ? strip_tags($config['Widget Suffix']) 
                        : "";
                    $view->assign('suffix', $suffix);

                    $prefix 
                        = ($config['Widget Prefix']) 
                        ? strip_tags($config['Widget Prefix']) 
                        : "";
                    $view->assign('prefix', $prefix);

                    $plan_ids = explode("|", $product['finance_plans']);
                    foreach ($plans_ids as $key=>$id) {
                        if('' == $id) unset($plans_ids[$key]);
                    }

                    if (empty($plans_ids)) {
                        $plans = PlansService::getStoredPlans();
                        if (empty($plans)) {
                            $sdkResponse = PlansService::getPlansFromSDK($config['API Key']);
                            if (false === $sdkResponse->error) {
                                $plans = $sdkResponse->plans;
                                PlansService::storePlans($plans);
                                $show_widget = true;
                            }
                        } else $show_widget = true;
                        
                        foreach ($plans as $plan) $plans_ids[] = $plan->getId();
                    } else $show_widget = true;

                    $view->assign('plans', implode(",", $plan_ids));
                }
            }
            $view->assign('show_widget', $show_widget);
        }
    }
}
