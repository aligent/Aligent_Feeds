<?php

/**
 * Translates category ids into a complete path.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Category {

    protected $_iStoreId = false;

    protected $_oCategories;
    protected $_aCategoryPaths = array();


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
            $this->_aCategoryPaths = array();
            $this->_initCategories($oStore);
            $this->_iStoreId = $oStore->getId();
        }

        return $this->_getCategoryPath($aRow[$vField]);
    }


    protected function _initCategories(Mage_Core_Model_Store $oStore) {
        $this->_oCategories = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStore($oStore)
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();
    }

    protected function _getCategoryPath($iCategoryId) {
        if (!array_key_exists($iCategoryId, $this->_aCategoryPaths)) {
            $oCategory = $this->_oCategories->getItemById($iCategoryId);
            if ($oCategory === null || $oCategory->getLevel() == 1) {
                $this->_aCategoryPaths[$iCategoryId] = '';
            } else {
                $vParentCategoryPath = $this->_getCategoryPath($oCategory->getParentId());
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