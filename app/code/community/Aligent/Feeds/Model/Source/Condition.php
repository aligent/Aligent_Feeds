<?php

/**
 * Based on code from: http://inchoo.net/ecommerce/magento/how-to-create-custom-attribute-source-type/
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Source_Condition extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    const CONDITION_NEW = 1;
    const CONDITION_USED = 2;
    const CONDITION_REFURBISHED = 3;

    public function getAllOptions() {
        return array(
            array(
                'label' => Mage::helper('aligent_feeds')->__('New'),
                'value' => self::CONDITION_NEW,
            ),
            array(
                'label' => Mage::helper('aligent_feeds')->__('Used'),
                'value' => self::CONDITION_USED,
            ),
            array(
                'label' => Mage::helper('aligent_feeds')->__('Refurbished'),
                'value' => self::CONDITION_REFURBISHED,
            ),
        );
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

    public function getOptionArray() {
        $aOptions = array();
        foreach ($this->getAllOptions() as $aOption) {
            $aOptions[$aOption['value']] = $aOption['label'];
        }
        return $aOptions;
    }

    public function getEbayValue($iOptionId) {
        switch ($iOptionId) {
            case self::CONDITION_NEW:
                return 'New';
            case self::CONDITION_USED:
                return 'Used';
            case self::CONDITION_REFURBISHED:
                return 'Refurbished';
            default:
                return '';
        }
    }

    public function getGoogleValue($iOptionId) {
        switch ($iOptionId) {
            case self::CONDITION_NEW:
                return 'new';
            case self::CONDITION_USED:
                return 'used';
            case self::CONDITION_REFURBISHED:
                return 'refurbished';
            default:
                return '';
        }
    }


    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $column['type']     = 'tinyint(1)';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_SMALLINT;
            $column['length']   = 1;
            $column['nullable'] = true;
            $column['comment']  = $attributeCode . ' column';
        }

        return array($attributeCode => $column);
    }


    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array($this->getAttribute()->getAttributeCode())
        );

        return $indexes;
    }


    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}