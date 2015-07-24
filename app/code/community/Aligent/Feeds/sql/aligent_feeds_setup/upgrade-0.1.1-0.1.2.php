<?php

$oCatalogSetup = new Mage_Catalog_Model_Resource_Setup('core_setup');
$oCatalogSetup->startSetup();

$oCatalogSetup->addAttribute('catalog_category', 'use_as_product_type_in_feed',  array(
    'group'    => 'General Information',
    'label'    => 'Use as Product Type in Feeds',
    'input'    => 'select',
    'type'     => 'int',
    'source'   => 'eav/entity_attribute_source_boolean',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required' => 1,
    'default'  => 1,
));

$oCatalogSetup->endSetup();