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
use FinancePlugin\Components\Finance\EnvironmentService;
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

        require_once($pluginDirectory.'/vendor/autoload.php');
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

        $config = Helper::getConfig();

        $apiKey = Helper::getApiKey();
        $key = preg_split("/\./", $apiKey);
        $view->assign('apiKey', $key[0]);


        $environment = EnvironmentService::retrieveEnvironmentFromDbByPluginId(1);
        if($environment) {
            $env = $environment->getEnvironment();
        }

        if(!isset($env)) {
            $environmentResponse = EnvironmentService::getEnvironmentResponse($apiKey);
            if($environmentResponse->Error == false) {
                $environment = EnvironmentService::constructEnvironmentFromResponse($environmentResponse);
                EnvironmentService::storeEnvironment($environment);
                $env = $environment->getEnvironment();
            }
        }

        if(!isset($env)) $env = 'divido';

        // Get environment from the database instead

        $view->assign('env', $env);

        if ($controller->Request()->getActionName() == 'index') {
            $product = $view->sArticle;

            $show_widget = false;
            if ($config['show_widget']) {

                $min_product_amount
                    = (isset($config['widget_minimum']))
                    ? $config['widget_minimum']*100
                    : 0;

                $product_price = filter_var(
                    $product['price'],
                    FILTER_SANITIZE_NUMBER_INT
                );

                if ($product_price > $min_product_amount) {

                    $view->assign('plans', implode(",", $plans_ids));

                    $button_txt
                        = (!($config['widget_button_text']))
                        ? ''
                        : "data-button-text='".strip_tags($config['widget_button_text'])."'";
                    $view->assign('widget_btn_txt', $button_txt);

                    $footnote
                        = (empty($config['widget_footnote']))
                        ? ""
                        : "data-footnote='".strip_tags($config['widget_footnote'])."'";
                    $view->assign('widget_footnote', $footnote);

                    $mode
                        = ($config['widget_mode'])
                        ? "data-mode='".$config['widget_mode']."'"
                        : "";
                    $view->assign('widget_mode', $mode);

                    $plan_ids = explode("|", $product['finance_plans']);
                    foreach ($plans_ids as $key=>$id) {
                        if('' == $id) unset($plans_ids[$key]);
                    }

                    if (empty($plans_ids)) {
                        $confPlans = Helper::getPlans();
                        $plans = PlansService::getStoredPlans();
                        if (empty($plans)) {
                            $sdkResponse = PlansService::getPlansFromSDK($config['api_key']);
                            if (false === $sdkResponse->error) {
                                $plans = $sdkResponse->plans;
                                PlansService::storePlans($plans);
                                $show_widget = true;
                            }
                        } elseif(count($confPlans) > 0){
                            foreach($plans as $key => $plan) {
                                if(!in_array($plan->getName(), $confPlans)){
                                    unset($plans[$key]);
                                }
                            }
                            // Only show the widget if there are still plans left after
                            // removing any not in the conf list
                            $show_widget = (count($plans) > 0) ? true : false;
                        } else $show_widget = true;

                        foreach ($plans as $plan) $plan_ids[] = $plan->getId();
                    } else $show_widget = true;

                    $view->assign('plans', implode(",", $plan_ids));
                }
            }

            $view->assign('show_widget', $show_widget);
        }
    }
}
