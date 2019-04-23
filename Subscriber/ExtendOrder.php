<?php
/**
 * File for the ExtendOrder class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use FinancePlugin\Components\Finance\EnvironmentService;
use FinancePlugin\Components\Finance\Helper;
use \Shopware_Components_Config;

/**
 * Backend listener which updates plans when the merchant heads
 * for the products section
 */
class ExtendOrder implements SubscriberInterface
{
    private $pluginDirectory;

    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;

        require_once($pluginDirectory.'/vendor/autoload.php');
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order'
                => 'onOrderPostDispatch'
        ];
    }

    public function onOrderPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Order $controller */
        $controller = $args->getSubject();

        $view = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($request->getActionName() == 'index') {
           $view->extendsTemplate('backend/finance_plugin/app.js');
        }

        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/finance_plugin/view/detail/overview.js');
        }
    }


}