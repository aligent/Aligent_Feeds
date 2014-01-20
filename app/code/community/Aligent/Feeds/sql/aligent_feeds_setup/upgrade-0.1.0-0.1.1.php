<?php
$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');

$attr = array (
    'attribute_model' => NULL,
    'backend' => NULL,
    'type' => 'int',
    'table' => NULL,
    'frontend' => NULL,
    'input' => 'select',
    'label' => 'Google Shopping Condition',
    'frontend_class' => NULL,
    'source' => 'aligent_feeds/source_condition',
    'required' => '0',
    'user_defined' => '1',
    'default' => '',
    'unique' => '0',
    'note' => NULL,
    'input_renderer' => NULL,
    'global' => '1',
    'visible' => '1',
    'searchable' => '0',
    'filterable' => '0',
    'comparable' => '0',
    'visible_on_front' => '0',
    'is_html_allowed_on_front' => '1',
    'is_used_for_price_rules' => '0',
    'filterable_in_search' => '0',
    'used_in_product_listing' => '1',
    'used_for_sort_by' => '0',
    'is_configurable' => '0',
    'apply_to' => NULL,
    'visible_in_advanced_search' => '0',
    'position' => '0',
    'wysiwyg_enabled' => '0',
    'used_for_promo_rules' => '0',
    'search_weight' => '1',
);

$setup->addAttribute('catalog_product', 'gshopping_condition', $attr);

$aAttributeSetIds = $setup->getAllAttributeSetIds('catalog_product');
$iAttributeId = $setup->getAttributeId('catalog_product', 'gshopping_condition');

foreach ($aAttributeSetIds as $iAttributeSetId) {
    $iAttributeGroupId = $setup->getAttributeGroupId('catalog_product', $iAttributeSetId, 'Meta Information');
    $setup->addAttributeToSet('catalog_product',$iAttributeSetId, $iAttributeGroupId, $iAttributeId, 6);
}
