<?php
/**
 * Feed Brand/Manufacturer Translator
 * Class Aligent_Feeds_Model_Translator_Brand
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
*/
class Aligent_Feeds_Model_Translator_Brand
{
    public function translate($aRow) {
        $brandId = $aRow['brand_category'];

        if(is_int($brandId)) {
            $oCategory = Mage::getModel('catalog/category')->load($brandId);
        }

        return ($oCategory) ? $oCategory->getName() : '';
    }
}