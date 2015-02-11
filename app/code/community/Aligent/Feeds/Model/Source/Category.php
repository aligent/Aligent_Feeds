<?php

/**
 * Config category source
 */
class Aligent_Feeds_Model_Source_Category {

    public function toOptionArray() {

        $oCategories = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addFieldToFilter('level', array('gt' => 1))
            ->addIsActiveFilter()
            ->load();

        $aOptions = array(array(
            'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
            'value' => ''
        ));

        foreach ($oCategories as $oCategory) {
            $aOptions[] = array(
                'label' => $oCategory->getName(),
                'value' => $oCategory->getId()
            );
        }

        return $aOptions;
    }
}
