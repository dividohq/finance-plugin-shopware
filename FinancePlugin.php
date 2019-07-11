<?php

/**
 * File for main Finance Plugin
 *
 * PHP version 5.5
 **/
namespace FinancePlugin;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Payment\Payment;
use FinancePlugin\Components\Finance\Helper;
use FinancePlugin\Components\Finance\TranslationService;
use FinancePlugin\Components\Finance\ShopwareTranslationService;

/**
 * Finance Plugin
 *
 * @category Payment_Gateway
 * @package  FinancePlugin
 * @since    File available since Release 1.0.0
 */
class FinancePlugin extends Plugin
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
            'name' => 'finance_plugin',
            'description' => 'Pay By Finance',
            'action' => 'FinancePlugin',
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
                $em->getClassMetadata(\FinancePlugin\Models\Plan::class),
                $em->getClassMetadata(\FinancePlugin\Models\Session::class),
                $em->getClassMetadata(\FinancePlugin\Models\Environment::class),
            ],
            true
        );

        $service->update(
            's_articles_attributes',
            'finance_plans',
            'multi_selection',
            [
                'entity' => \FinancePlugin\Models\Plan::class,
                'displayInBackend' => true,
                'label' => 'Finance Plans',
                'supportText' => 'The plans available to the merchant',
                'helpText' => 'Finance Plans'
            ]
        );

        ShopwareTranslationService::expungeTerms();

        $translationService = new ShopwareTranslationService('cf61730cc2b9ba407fdf6387f0e08c2b', '267665', 'de');
        $response = $translationService->getTranslationResponse();
        try {
            $terms = $translationService->getResponseTerms($response);
            $localeId = $translationService->getLocaleId();
            $translationService->importTerms($terms, $localeId);
        } catch (Exception $e) {
            Helper::debug($e->getMessage(), 'error');
        }

        $translationService->setLanguage('en');
        $response = $translationService->getTranslationResponse();
        try {
            $terms = $translationService->getResponseTerms($response);
            $localeId = $translationService->getLocaleId();
            $translationService->importTerms($terms, $localeId);
        } catch (Exception $e) {
            Helper::debug($e->getMessage(), 'error');
        }

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

        if(!$context->keepUserData()){
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->delete('s_order_basket_attributes', 'finance_id');
            $service->delete('s_order_basket_attributes', 'deposit_value');
            $service->delete('s_articles_attributes', 'finance_plans');

            $em = $this->container->get('models');
            $schemaTool = new SchemaTool($em);
            $schemaTool->dropSchema(
                [
                    $em->getClassMetadata(\FinancePlugin\Models\Plan::class),
                    $em->getClassMetadata(\FinancePlugin\Models\Session::class),
                    $em->getClassMetadata(\FinancePlugin\Models\Environment::class),
                ],
                true
            );

        }

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
            $payment->setActive($active);
        }
        $em->flush();
    }
}
