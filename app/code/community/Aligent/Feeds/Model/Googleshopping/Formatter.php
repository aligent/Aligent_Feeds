<?php

/**
 * Prepares catalog flat data for the Google Shopping feed export
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Googleshopping_Formatter {
    const CONFIG_CONDITION = 'google/shopping/condition';

    protected $_iStoreId;

    protected $_vBaseUrl;
    protected $_vMediaBaseUrl;

    protected $_iDefaultCondition;

    protected $_oCategories;
    protected $_aCategoryPaths = array();

    /**
     * Called once at that the beginning of the export.  Used this to perform any
     * actions required (e.g. loading categories) to set up the process.
     *
     * @param Mage_Core_Model_Store $oStore The store context in which this export occurs
     * @return Aligent_Feeds_Model_Googleshopping_Formatter $this
     */
    public function init(Mage_Core_Model_Store $oStore) {
        $this->_iStoreId = $oStore->getId();
        $this->_vBaseUrl = $oStore->getBaseUrl();
        $this->_vMediaBaseUrl = $oStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $this->_iDefaultCondition = Mage::getStoreConfig(self::CONFIG_CONDITION, $oStore->getId());

        $this->_initCategories($oStore);

        return $this;
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

    /**
     * Formats a single row of catalog flat data for use in the Google Shopping feed
     *
     * @param array $aRow A row from the catalog_product_flat_x table.
     * @return array|bool False if this product shouldn't be exported.  Array of data ready to be written to CSV otherwise.
     */
    public function prepareRow($aRow) {
        $availability = Mage::getSingleton('modcatalog/system_config_source_availability')->getGoogleAvailability($aRow['availability']);
        if ($availability == Aligent_Catalog_Model_System_Config_Source_Availability::GOOGLE_NOT_IN_FEED) {
            return false;
        }

        $aRet = array(
            'identifier' => $aRow['sku'].'-'.$this->_iStoreId,  // The ID we give to google must be
                                                // unique across all feeds, so append the store ID
                                                // to ensure uniqueness in a multistore environment
            'name' => $aRow['name'],
            'description' => $aRow['short_description'],
            'gshopping_category' => $aRow['gshopping_category_value'],
            'category' => $this->_getCategoryPath($aRow['category_id']),
            'url' => $this->_vBaseUrl.$aRow['url_path'],
            'image' => ($aRow['small_image'] != '' && $aRow['small_image'] != 'no_selection' ? $this->_vMediaBaseUrl.'catalog/product/'.$aRow['small_image'] : ''),
            'gshopping_condition' => Mage::getSingleton('aligent_feeds/source_condition')->getGoogleValue($aRow['gshopping_condition'] ? $aRow['gshopping_condition'] : $this->_iDefaultCondition),
            'availability' => $availability,
            'price' => $aRow['price'],
            'special_price' => $aRow['special_price'],
            'special_price_date' => $this->getIso8601Date($aRow['special_from_date']).'/'.$this->getIso8601Date($aRow['special_to_date']),
            'brand' => Mage::getSingleton('aligentbrand/source_option')->getOptionText($aRow['brand_category']),
            'sku' => ($aRow['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ? $aRow['sku'] : ''), // Locally defined bundles and configurables don't have a global identifier
            'identifier_exists' => ($aRow['type_id'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ? 'TRUE' : 'FALSE'), // Locally defined bundles and configurables don't have a global identifier
        );

        return $aRet;
    }

    protected function getIso8601Date($vMySqlDate) {
        return date('c', strtotime($vMySqlDate));
    }
}