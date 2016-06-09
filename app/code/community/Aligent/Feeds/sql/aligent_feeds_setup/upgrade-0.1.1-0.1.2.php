<?php
/**
 * Add GTIN attribute
 * @author    William Tran <william@aligent.com.au>
 * @copyright 2016 Aligent Consulting.
 * @license   All Rights Reserved
 * @link      http://www.aligent.com.au/
 */

/** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Setup $installer */
$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
$installer->startSetup();

$vType = 'catalog_product';
$vGroupName = 'Meta Information';

// Create Precautionary Attribute
$vAttrCode = 'gtin';
$vAttrName = 'GTIN';
$aAttrData = array(
    'label'                     => $vAttrName,
    'global'                    => true,
    'input'                     => 'text',
    'is_configurable'           => false,
    'group'                     => $vGroupName,
    'user_defined'              => true,
    'required'                  => false,
    'visible'                   => '1',
    'searchable'                => true,
    'filterable'                => true,
    'comparable'                => true,
    'filterable_in_search'      => false,
    'used_in_product_listing'   => true,
    'is_used_for_promo_rules'   => false,
    'visible_on_front'          => '0',
    'is_html_allowed_on_front'  => '1'
);
$installer->addAttribute($vType,$vAttrCode,$aAttrData);


$installer->endSetup();