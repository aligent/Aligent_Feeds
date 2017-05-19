<?php

/**
 * Performs conversion
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Translate {

    /**
     * Translate Field to price
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the price is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function price($aRow, $vField, $oStore) {
        //price is 0/null useful in case of special_price
        if (empty($aRow[$vField])){
            return null;
        }
        $fPrice= $aRow[$vField];
        /**
         * @see Mage_Core_Helper_Data::currencyByStore()  (static function)
         */
        $fConvertedPriced = Mage::helper('core')->currencyByStore($fPrice,$oStore,false,false);
        return round($fConvertedPriced,2) . ' ' . $oStore->getCurrentCurrencyCode();
    }

}