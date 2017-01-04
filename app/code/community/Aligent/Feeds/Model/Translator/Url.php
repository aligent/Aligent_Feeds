<?php

/**
 * Singleton class to return product URL.
 * Since Magento 1.13.1 url_path is no longer used. In which case Product Url is determined using unique url_key and url suffix (.html)
 */
class Aligent_Feeds_Model_Translator_Url {
    public function translate($aRow, $vField, $oStore) {
        $vUrl = $oStore->getBaseUrl();
        if (isset($aRow['url_path']) && $aRow['url_path']) {
            $vUrl .= $aRow['url_path'];
        } else {
            $vProductUrlSuffix = Mage::helper('catalog/product')->getProductUrlSuffix();
            if (Mage::getEdition() === Mage::EDITION_ENTERPRISE) {
                // Magento Community expects the product url suffix to include a . (see Mage_Catalog_Model_Url::getProductRequestPath)
                // Magento Enterprise prepend the . automatically (see Enterprise_Catalog_Helper_Data::getProductRequestPath)
                $vProductUrlSuffix = '.' . $vProductUrlSuffix;
            }
            $vUrl .= $aRow['url_key'] . $vProductUrlSuffix;
        }
        return $vUrl;
    }
}