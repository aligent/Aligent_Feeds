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

        return $this;
    }

    /**
     * Formats a single row of catalog flat data for use in the Google Shopping feed
     *
     * @param array $aRow A row from the catalog_product_flat_x table.
     * @return array|bool False if this product shouldn't be exported.  Array of data ready to be written to CSV otherwise.
     */
    public function prepareRow($aRow) {
        $aRet = array();

        foreach ($this->_oConfig->children() as $vKey => $oFieldConfig) {
            $vValue = '';
            foreach ($oFieldConfig->children() as $vType => $data) {
                if (substr($vType, 0, 9) == 'attribute') {
                    $vAttribute = (string) $data;
                    if (array_key_exists($vAttribute, $aRow)) {
                        $vValue .= $aRow[$vAttribute];
                    }
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
                    }
                } elseif (substr($vType, 0, 9) == 'singleton') {
                    $vClass = (string) $data->class;
                    $vMethod = (string) $data->method;
                    $vField = (string) $data->field;

                    $vValue .= Mage::getSingleton($vClass)->{$vMethod}($aRow, $vField, $this->_oStore);
                }
            }
            $aRet[$vKey] = $vValue;
        }

       return $aRet;
    }

}