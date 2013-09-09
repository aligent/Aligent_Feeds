<?php

/**
 * Performs translations specific to the "Condition" value for Google Shopping.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Condition {
    const CONFIG_CONDITION = 'feeds/googleshopping/condition';

    protected $_iDefaultCondition;
    protected $_iStoreId = false;

    /**
     * Translates a category id stored in the supplied field to a full category path.
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the category id is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore) {
        if ($oStore->getId() !== $this->_iStoreId) {
            $this->_iDefaultCondition = Mage::getStoreConfig(self::CONFIG_CONDITION, $oStore->getId());
            $this->_iStoreId = $oStore->getId();
        }

        $vFieldValue = false;
        if (array_key_exists($vField, $aRow)) {
            $vFieldValue = $aRow[$vField];
        }
        return Mage::getSingleton('aligent_feeds/source_condition')->getGoogleValue($vFieldValue ? $vFieldValue : $this->_iDefaultCondition);
    }
}