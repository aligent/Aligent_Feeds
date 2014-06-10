<?php

/**
 * Singleton class to return product URL.
 * Since Magento 1.13.1 url_path is no longer used. In which case Product Url is determined using unique url_key and url suffix (.html)
 */
class Aligent_Feeds_Model_Translator_Url{
    public function translate($aRow, $vField, $oStore) {
        $vUrl = $oStore->getBaseUrl();
        if ($aRow['url_path']) {
            $vUrl .= $aRow['url_path'];
        } else {
            $vUrl .= $aRow['url_key'].'.'.Mage::helper('catalog/product')->getProductUrlSuffix();
        }
        return $vUrl;
    }
}