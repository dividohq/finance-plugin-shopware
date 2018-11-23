<?php

namespace FinancePlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use FinancePlugin\Models\Plan;
use FinancePlugin\Components\Finance\PlansService;

class UpdatePlans implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Article' => 'onArticlePostDispatch',
        ];
    }

    public function onArticlePostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Article $controller */
        $controller = $args->getSubject();

        $view = $controller->View();
        $request = $controller->Request();
        
        if ($request->getActionName() == 'index') {
            $this->set_plans();
        }
        
    }

    private function set_plans(){
        $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader')
            ->getByPluginName('FinancePlugin');
        if(!empty($config["Api Key"]))
        {
            $plans = PlansService::updatePlans();
        }
    }
}
