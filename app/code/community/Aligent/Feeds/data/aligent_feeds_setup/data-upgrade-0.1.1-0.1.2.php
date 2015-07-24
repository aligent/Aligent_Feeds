<?php

/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* @var $oCategoryResource Mage_Catalog_Model_Resource_Category */
$oCategoryResource = Mage::getModel('catalog/category')->getResource();

//Set all existing categories to true for 'use_as_product_type_in_feed'
$cCategories = Mage::getModel('catalog/category')->getCollection();
foreach ($cCategories as $oCategory) {
    $oCategory->setUseAsProductTypeInFeed(1);
    $oCategoryResource->saveAttribute($oCategory, 'use_as_product_type_in_feed');
}

/* End script */
$installer->endSetup();