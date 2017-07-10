<?php

class Aligent_Feeds_Model_Translator_Categories
{
    /**
     * Finds all the products categories and returns them all seperated by a pipe.
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the category id is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @param array $params Any additional parameters
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore, $params) {
        $oProduct = Mage::getModel('catalog/product');
        $oProduct->setId($aRow['entity_id']);
        $cCategories = $oProduct->getResource()
            ->getCategoryCollection($oProduct)
            ->addIsActiveFilter();

        $aPaths = array();

        foreach($cCategories as $oCategory) {
            $aPaths[] = Mage::helper('aligent_feeds')->getCategoryPath($oCategory->getEntityId(), $oStore);
        }

        $vSeparator = '|';
        if (isset($params['separator'])) {
            $vSeparator = $params['separator'];
        }

        return implode($vSeparator, $aPaths);
    }
}