<?php

class Aligent_Feeds_Model_Translator_Brand
{
    public function translate($aRow) {
        $brandId = $aRow['brand_category'];

        if($brandId != '') {
            $oCategory = Mage::getModel('catalog/category')->load($brandId);
        }

        return ($oCategory) ? $oCategory->getName() : '';
    }
}