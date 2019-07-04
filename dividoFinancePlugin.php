<?php

/**
 * File for main Finance Plugin
 *
 * PHP version 5.5
 **/
namespace dividoFinancePlugin;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Payment\Payment;
use dividoFinancePlugin\Components\Finance\Helper;

/**
 * Divido Finance Plugin
 *
 * @category Payment_Gateway
 * @package  dividoFinancePlugin
 * @since    File available since Release 1.4.2
 */
class dividoFinancePlugin extends Plugin
{
    /**
     * Install context
     *
     * @param InstallContext $context The install context
     *
     * @return void
     */
    public function install(InstallContext $context)
    {
        /*
         * @var \Shopware\Components\Plugin\PaymentInstaller $installer Installer
         */
        $installer = $this->container->get('shopware.plugin_payment_installer');
        $options = [
            'name' => 'divido_finance_plugin',
            'description' => 'Pay By Finance',
            'action' => 'dividoFinancePlugin',
            'active' => 1,
            'position' => 0,
            'additionalDescription' =>
                '<div id="payment_desc">'
                . 'Finance your cart'
                . '</div>'
        ];

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update(
            's_order_basket_attributes',
            'deposit_value',
            'float',
            [
            'displayInBackend' => true,
            'label' => 'Deposit Value',
            'supportText' => 'The value of the deposit taken',
            'helpText' => 'Deposit value'
            ]
        );
        $service->update(
            's_order_basket_attributes',
            'finance_id',
            'string',
            [
            'displayInBackend' => true,
            'label' => 'Finance',
            'supportText' => 'The ID of the finance ',
            'helpText' => 'Finance ID'
            ]
        );

        $em = $this->container->get('models');
        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema(
            [
                $em->getClassMetadata(\dividoFinancePlugin\Models\Plan::class),
                $em->getClassMetadata(\dividoFinancePlugin\Models\Session::class),
                $em->getClassMetadata(\dividoFinancePlugin\Models\Environment::class),
            ],
            true
        );

        $service->update(
            's_articles_attributes',
            'finance_plans',
            'multi_selection',
            [
                'entity' => \dividoFinancePlugin\Models\Plan::class,
                'displayInBackend' => true,
                'label' => 'Finance Plans',
                'supportText' => 'The plans available to the merchant',
                'helpText' => 'Finance Plans'
            ]
        );

        $installer->createOrUpdate($context->getPlugin(), $options);
    }

    /**
     * Uninstall context
     *
     * @param UninstallContext $context The Uninstall context
     *
     * @return void
     */
    public function uninstall(UninstallContext $context)
    {
        $this->_setActiveFlag($context->getPlugin()->getPayments(), false);

        $service = $this->container->get('shopware_attribute.crud_service');
        $em = $this->container->get('models');

        if ($service->get('s_core_paymentmeans_attributes', 'divido_finance_plugin') !== null) {

            $service->delete(
                's_core_paymentmeans_attributes',
                'divido_finance_plugin'
            );
        }
        $em->generateAttributeModels(['s_core_paymentmeans_attributes']);

        Shopware()->Db()->query("UPDATE `s_core_paymentmeans` SET `active`=0, `hide`=1 WHERE `action`='dividoFinancePlugin' LIMIT 1");

        if($context->keepUserData()){
            parent::uninstall($context);
            $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
            return;
        }

        $service->delete('s_order_basket_attributes', 'finance_id');
        $service->delete('s_order_basket_attributes', 'deposit_value');
        $service->delete('s_articles_attributes', 'finance_plans');

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(
            [
                $em->getClassMetadata(\dividoFinancePlugin\Models\Plan::class),
                $em->getClassMetadata(\dividoFinancePlugin\Models\Session::class),
                $em->getClassMetadata(\dividoFinancePlugin\Models\Environment::class),
            ],
            true
        );

        parent::uninstall($context);

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * Deactivating Plugin
     *
     * @param DeactivateContext $context Context
     *
     * @return void
     */
    public function deactivate(DeactivateContext $context)
    {
        $this->_setActiveFlag($context->getPlugin()->getPayments(), false);
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * Activating Plugin context
     *
     * @param ActivateContext $context Context
     *
     * @return void
     */
    public function activate(ActivateContext $context)
    {
        $this->_setActiveFlag($context->getPlugin()->getPayments(), true);
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * Set Active
     *
     * @param Payment[] $payments Activate in payments
     * @param bool      $active   Active/Inactive
     *
     * @return void
     */
    private function _setActiveFlag($payments, $active)
    {
        $em = $this->container->get('models');

        foreach ($payments as $payment) {
            $payment->setActive(0);
        }
    }
}
