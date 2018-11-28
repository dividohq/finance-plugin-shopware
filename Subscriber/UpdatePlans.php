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

/**
 * Backend listener which updates plans when the merchant heads
 * for the products section
 */
class UpdatePlans implements SubscriberInterface
{
    /**
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
        /** 
         * @var \Shopware_Controllers_Backend_Article $controller
         **/
        $controller = $args->getSubject();

        $view = $controller->View();
        $request = $controller->Request();
        
        if ($request->getActionName() == 'index') {
            $this->_setPlans();
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
        /** 
         * @var \Shopware_Controllers_Backend_Config $controller
         **/
        $controller = $args->getSubject();

        $request = $controller->Request();

        if ($request->getActionName() == 'saveForm') {
            PlansService::clearPlans();
        }
    }

    /**
     * Use the PlansService class to update plans
     * if we have an API key
     *
     * @return void
     */
    private function _setPlans()
    {
        $config = Shopware()
            ->Container()
            ->get('shopware.plugin.cached_config_reader')
            ->getByPluginName('FinancePlugin');
        if (!empty($config["Api Key"])) {
            // $plans = PlansService::updatePlans();
            // TODO: Rewrite UpdatePlans
        }
    }


}
