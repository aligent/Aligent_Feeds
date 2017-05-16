<?php

/**
 * Prepares catalog flat data for the Google Shopping feed export
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Feed_Formatter {

    protected $_oStore;

    protected $_vBaseUrl;
    protected $_vMediaBaseUrl;
    protected $_vProductMediaBaseUrl;
    protected $_oConfig;

    /**
     * Called once at that the beginning of the export.  Used this to perform any
     * actions required (e.g. loading categories) to set up the process.
     *
     * @param Mage_Core_Model_Store $oStore The store context in which this export occurs
     * @param Mage_Core_Model_Config_Element $oConfig The field mapping for this feed
     * @return Aligent_Feeds_Model_Googleshopping_Formatter $this
     */
    public function init(Mage_Core_Model_Store $oStore, Mage_Core_Model_Config_Element $oConfig) {
        $this->_oConfig = $oConfig;
        $this->_oStore = $oStore;
        $this->_vBaseUrl = $oStore->getBaseUrl();
        $this->_vMediaBaseUrl = $oStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $this->_vProductMediaBaseUrl = $this->_vMediaBaseUrl . 'catalog/product';

        return $this;
    }

    /**
     * Formats a single row of catalog flat data for use in the data feed
     *
     * @param array $aDbRow A row from the catalog_product_flat_x table.
     * @return array|bool False if this product shouldn't be exported.  Array of data ready to be written to CSV otherwise.
     */
    public function prepareRow($aDbRow) {
        $aFeedRow = array();

        foreach ($this->_oConfig->fields->children() as $vKey => $oFieldConfig) {
            // If the field's definition includes an "<exclude />" tag, remove it from the export data.
            foreach ($oFieldConfig->children() as $vType => $data) {
                if ($vType == 'exclude') {
                    continue(2);
                }
            }
            $vValue = '';
            foreach ($oFieldConfig->children() as $vType => $data) {
                if ((string)$data === '__ignore__') {
                    continue;
                }
                if (substr($vType, 0, 9) == 'attribute') {
                    $vAttribute = (string) $data;
                    $vAttributeValue = '';
                    if (array_key_exists($vAttribute, $aDbRow)) {
                        $vAttributeValue .= $aDbRow[$vAttribute];
                    }
                    if ($data->getAttribute('defaultValue') && $vAttributeValue == '') {
                        $vAttributeValue = (string) $data->getAttribute('defaultValue');
                    }
                    $vValue .= $vAttributeValue;

                } elseif (substr($vType, 0, 5) == 'value') {
                    $vValue .= (string) $data;
                } elseif (substr($vType, 0, 7) == 'special') {
                    switch ((string) $data) {
                        case 'store_id';
                            $vValue .= $this->_oStore->getId();
                            break;
                        case 'base_url':
                            $vValue .= $this->_vBaseUrl;
                            break;
                        case 'media_base_url':
                            $vValue .= $this->_vMediaBaseUrl;
                            break;
                        case 'product_media_base_url':
                            $vValue .= $this->_vProductMediaBaseUrl;
                            break;
                    }
                } elseif (substr($vType, 0, 9) == 'singleton') {
                    $vClass = (string) $data->class;
                    $vMethod = (string) $data->method;
                    $vField = (string) $data->field;
                    $aParams = (array) $data->params;
                    $bRemoved = isset($data->remove);

                    if (!$bRemoved) {
                        $vSingletonValue = Mage::getSingleton($vClass)->{$vMethod}($aDbRow, $vField, $this->_oStore, $aParams);
                        if (is_array($vSingletonValue)) {
                            $vValue = $vSingletonValue;
                        } else {
                            $vValue .= $vSingletonValue;
                        }
                    }
                }
            }
            $aFeedRow[$vKey] = $vValue;
        }

        // Wrap the generated row into an array.  The after_item_filter might
        // want to export several copies of this item (e.g. for configurable
        // products)
        $aFeedRows = array(0 => $aFeedRow);

        // Allow the feed definition to include an "after_item_filter".  This method
        // will be allowed to modify the generated rows (including adding more) before
        // it's exported.
        if ($this->_oConfig->after_item_filter) {
            $vClass = (string) $this->_oConfig->after_item_filter->class;
            $vMethod = (string) $this->_oConfig->after_item_filter->method;
            $aParams = (array) $this->_oConfig->after_item_filter->params;

            $aFeedRows = Mage::getSingleton($vClass)->{$vMethod}($aDbRow, $aFeedRows, $this->_oStore, $aParams);
        }

       return $aFeedRows;
    }

}