<?php
/**
 * File for the UpdatePlans class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use FinancePlugin\Models\Plan;
use FinancePlugin\Components\Finance\PlansService;
use FinancePlugin\Components\Finance\EnvironmentService;
use FinancePlugin\Components\Finance\Helper;
use \Shopware_Components_Config;

/**
 * Backend listener which updates plans when the merchant heads
 * for the products section
 */
class UpdatePlans implements SubscriberInterface
{
    /**
     * The directory of the plugin
     *
     * @var string
     */
    private $_pluginDirectory;

    /**
     * Class constructor
     *
     * @param string $pluginDirectory The plugin location
     *
     * @return void;
     */
    public function __construct($pluginDirectory)
    {
        $this->_pluginDirectory = $pluginDirectory;

        require_once($pluginDirectory.'/vendor/autoload.php');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Article'
                => 'onArticlePostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Config'
                => 'onConfigPostDispatch',
        ];
    }

    /**
     * Set plans if we are in the index
     *
     * @param \Enlight_Event_EventArgs $args Arguments
     *
     * @return void
     */
    public function onArticlePostDispatch(\Enlight_Event_EventArgs $args)
    {
        $controller = $args->getSubject();

        $request = $controller->Request();

        if ($request->getActionName() == 'index') {
            $apiKey = Helper::getApiKey();
            $view = $controller->View();

            if (!empty($apiKey)) {
                $plans = PlansService::getStoredPlans();
                if (empty($plans)) {
                    $this->_refreshPlans($apiKey, $view);
                }
                $this->_setEnvironment($apiKey, $view);

            } else {
                $view->addTemplateDir($this->_pluginDirectory.'/Resources/views');
                $view->extendsTemplate('backend/fp_extend_config/view/');
                $view->assign([
                    'success' => false,
                    'message'  =>   'API key not entered. You will not be
                                    able to use this plugin as a payment method'
                ]);
            }
        }
    }

    /**
     * Remove plans if config updated
     *
     * @param \Enlight_Event_EventArgs $args Arguments
     *
     * @return void
     */
    public function onConfigPostDispatch(\Enlight_Event_EventArgs $args)
    {
        Helper::log('Updating Config', 'info');
        $controller = $args->getSubject();

        $request = $controller->Request();

        if ($request->getActionName() == 'saveForm') {
            PlansService::clearPlans();
            $apiKey = Helper::getApiKey();

            $this->_refreshPlans($apiKey, $controller->View());

            $this->_setEnvironment($apiKey, $controller->View());
        }
    }

    /**
     * Function for common process of looking up plans from the SDK
     * and validating the API Key
     *
     * @param string $apiKey The API Key in the config
     * @param View   $view   Smarty view
     *
     * @return void
     */
    private function _refreshPlans($apiKey, $view)
    {
        $sdkResponse = PlansService::getPlansFromSDK($apiKey);

        if ($sdkResponse->error === false) {
            $plans = $sdkResponse->plans;
            if (empty($plans)) {
                $view->assign(
                    ['success' => true,
                    "message" => "There are no finance plans associated
                    to this API Key. This plugin will not be able to
                    process payments until this is rectified"]
                );
            } else {
                PlansService::storePlans($plans);
            }
        } else {
            $view->assign('message', 'This API Key could not be validated');
        }
    }

    private function _setEnvironment($apiKey, $view) {
        $environmentResponse = EnvironmentService::getEnvironmentResponse($apiKey);
        if($environmentResponse->Error == false) {
            $environment = EnvironmentService::constructEnvironmentFromResponse($environmentResponse);
            EnvironmentService::storeEnvironment($environment);

            $imgUrl = "https://s3-eu-west-1.amazonaws.com/content.divido.com/plugins/powered-by-divido/".
                $environment->getEnvironment().
                "/shopware/images/logo.png";
            if(file_exists($imgUrl)) {
                $destination = "./plugin.png";
                file_put_contents($imgUrl, $destination);
            }

        } else {
            Helper::log('Could not get environment', 'error');
            $view->addTemplateDir($this->_pluginDirectory.'/Resources/views');
            $view->extendsTemplate('backend/fp_extend_config/view/');
            $view->assign([
                'success' => false,
                'message'  =>   'Could not fetch your merchant environment.
                                Please consult your payment provider'
            ]);
        }
    }
}
