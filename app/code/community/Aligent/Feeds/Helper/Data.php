<?php

/**
 * Aligent Feeds Module
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_iStoreId = null;
    protected $_oCategories = null;
    protected $_aCategoryPaths = array();

    protected function _initCategories(Mage_Core_Model_Store $oStore) {
        $this->_oCategories = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId($oStore->getId())
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();
    }

    public function getCategoryPath($iCategoryId, $oStore=null) {
        if ($oStore === null) {
            $oStore = Mage::app()->getStore();
        }

        if ($oStore->getId() !== $this->_iStoreId) {
            $this->_aCategoryPaths = array();
            $this->_initCategories($oStore);
            $this->_iStoreId = $oStore->getId();
        }

        if (!array_key_exists($iCategoryId, $this->_aCategoryPaths)) {
            $oCategory = $this->_oCategories->getItemById($iCategoryId);
            if ($oCategory === null || $oCategory->getLevel() == 1) {
                $this->_aCategoryPaths[$iCategoryId] = '';
            } else {
                $vParentCategoryPath = $this->getCategoryPath($oCategory->getParentId(), $oStore);
                if ($vParentCategoryPath == '') {
                    $this->_aCategoryPaths[$iCategoryId] = $oCategory->getName();
                } else {
                    $this->_aCategoryPaths[$iCategoryId] = $vParentCategoryPath . ' > ' . $oCategory->getName();
                }
            }
        }
        return $this->_aCategoryPaths[$iCategoryId];
    }
}