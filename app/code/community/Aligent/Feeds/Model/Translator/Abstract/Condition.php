<?php

/**
 * Performs translations specific to the "Condition" value.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
abstract class Aligent_Feeds_Model_Translator_Abstract_Condition {
    protected $_vConfigHandle; //Must be defined in inheriting class
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
            $this->_iDefaultCondition = Mage::getStoreConfig($this->_vConfigHandle, $oStore->getId());
            $this->_iStoreId = $oStore->getId();
        }

        $vFieldValue = false;
        if (array_key_exists($vField, $aRow)) {
            $vFieldValue = $aRow[$vField];
        }

        return $this->_getSourceValue($vFieldValue ? $vFieldValue : $this->_iDefaultCondition);
    }

    /**
     * @param $vFieldValue
     * @return mixed
     *
     * Get Source Value for specific implementation
     */
    abstract function _getSourceValue($vFieldValue);
}