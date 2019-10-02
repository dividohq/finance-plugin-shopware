<?php
/**
 * File for TranslationService class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

use Shopware\Models\Snippet\Snippet;

class ShopwareTranslationService {

    const NAMESPACE_PREFIX = "widgets/finance_plugin/";

    private static $locales = array(
        'en' => 'en_GB',
        'de' => 'de_DE',
        'fr' => 'fr_FR',
        'es' => 'es_ES'
    );

    public static function importTerms(array $terms, $localeId) {

        foreach ($terms as $key=>$term) {
            $snippet = new Snippet;
            $snippet->setValue($term['definition']);
            $snippet->setName($key);

            $snippet->setShopId(1);

            $snippet->setLocaleId($localeId);

            $snippet->setNamespace(self::NAMESPACE_PREFIX.$term['context']);

            $snippet->setDirty(false);

            try {
                Shopware()->Models()->persist($snippet);
                Shopware()->Models()->flush();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public static function getLocaleId($code) {

        $lookup = (array_key_exists($code, self::$locales)) ? self::$locales[$code] : false;

        if(false === $lookup) {
            throw new Exception("Could not find locale from language {$code}");
        }

        $locale_sql = "
            SELECT `id`
            FROM `s_core_locales`
            WHERE `locale` = :lookup
            LIMIT 1
        ";

        $locales = Shopware()->Db()->query(
            $locale_sql,
            [':lookup' => $lookup]
        );

        foreach ($locales as $locale) {
            return $locale['id'];
        }

        return false;
    }

    public static function expungeTerms() {
        $delete_sql = "
            DELETE
            FROM `s_core_snippets`
            WHERE `namespace` LIKE :prefix
        ";

        $locales = Shopware()->Db()->query(
            $delete_sql,
            [':prefix' => self::NAMESPACE_PREFIX."%"]
        );

        return;
    }


}