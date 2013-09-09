<?php

/**
 * Feed generator class
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Feed {

    protected $_oWriters = array();

    public function export(Mage_Core_Model_Store $oStore, $vFeedname, Mage_Core_Model_Config_Element $oConfig) {

        Mage::getSingleton('aligent_feeds/log')->log("Beginning $vFeedname export for store #".$oStore->getId()." - ".$oStore->getName());
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
        Mage::getSingleton('aligent_feeds/log')->log("Initialising file writers...");
        $this->_initWriters($oStore, $vFeedname, $oConfig);

        // Prepare the csv file header
        Mage::getSingleton('aligent_feeds/log')->log("Begin preparing header rows...");
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
        $this->_prepareHeaders($oConfig);

        // Initialise the formatter
        Mage::getSingleton('aligent_feeds/log')->log("Initialising Feed Formatter...");
        Mage::getSingleton('aligent_feeds/feed_formatter')->init($oStore, $oConfig->fields);
        Mage::getSingleton('aligent_feeds/log')->log("Initialised Feed Formatter.");
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();

        $oConn = Mage::getModel('core/resource')->getConnection('catalog_read');
        $vCategoryProductTable = Mage::getModel('core/resource_setup', 'core_setup')->getTable('catalog/category_product');
        $vCategoryFlatTable = Mage::getResourceSingleton('catalog/category_flat')->getMainStoreTable($oStore->getId());
        $vProductFlatTable = Mage::getResourceModel('catalog/product_flat_indexer')->getFlatTableName($oStore->getId());

        // Complicated subquery to get the most deeply nested category that this
        // product is assigned to.  Picking the most deeply nested on the assumption
        // that the deepest category is most likely to be the most specific.
        $oSubSelect = new Varien_Db_Select($oConn);
        $oSubSelect
            ->from(array('ccf' => $vCategoryFlatTable), 'entity_id')
            ->joinInner(array('ccp2' => 'catalog_category_product'), 'ccf.entity_id=ccp2.category_id', array())
            ->where('ccp2.product_id=main_table.entity_id')
            ->where('ccf.is_active=1')
            ->order('level', Zend_Db_Select::SQL_DESC)
            ->limit(1);

        $oSelect = new Varien_Db_Select($oConn);
        $oSelect
            ->from(array('main_table' => $vProductFlatTable),
                array(
                    'main_table.*',
                    'category_id' => new Zend_Db_Expr('('.$oSubSelect.')')
                )
            )->where('visibility IN (?)', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH));

        Mage::getSingleton('aligent_feeds/log')->log("Exporting products...");
        $oResource = Mage::getModel('core/resource_iterator')->walk($oSelect, array(
            function($aArgs) {
                Mage::getSingleton('aligent_feeds/log')->log("Exporting product #".$aArgs['idx']."  SKU: ".$aArgs['row']['sku'], Zend_Log::DEBUG, true);
                if (($aArgs['idx'] % 100) == 0) {
                    Mage::getSingleton('aligent_feeds/log')->log("Exporting product #".$aArgs['idx']."...", Zend_Log::INFO);
                    Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
                }

                $aExportableRow = Mage::getSingleton('aligent_feeds/feed_formatter')->prepareRow($aArgs['row']);
                if ($aExportableRow !== false) {
                    foreach ($aArgs['writers'] as $oWriter) {
                        $oWriter->writeDataRow($aExportableRow);
                    }
                }
            }), array(
                'writers' => $this->_oWriters,
            ));

        $this->_closeWriters();
        Mage::getSingleton('aligent_feeds/log')->log("Finished $vFeedname data export for store #".$oStore->getId()." - ".$oStore->getName());
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
        return $this;
    }


    protected function _initWriters(Mage_Core_Model_Store $oStore, $vFeedname, Mage_Core_Model_Config_Element $oConfig) {
        foreach ($oConfig->output->children() as $oOutputFile) {
            switch (trim($oOutputFile->format)) {
                case 'csv':
                    $oWriter = Mage::getModel('aligent_feeds/writer_csv')->init($oStore->getCode(), $vFeedname, $oOutputFile);
                    if ($oWriter instanceof Aligent_Feeds_Model_Writer_Abstract) {
                        $this->_oWriters[] = $oWriter;
                    }
                    break;
                case 'xml':
                    $oWriter = Mage::getModel('aligent_feeds/writer_xml')->init($oStore->getCode(), $vFeedname, $oOutputFile);
                    if ($oWriter instanceof Aligent_Feeds_Model_Writer_Abstract) {
                        $this->_oWriters[] = $oWriter;
                    }
                    break;
            }
        }
        return $this;
    }


    /**
     * Extract header details from XML and write to file(s)
     *
     * @param Mage_Core_Model_Config_Element $oConfig XML baed field mapping
     * @return $this
     */
    protected function _prepareHeaders(Mage_Core_Model_Config_Element $oConfig) {
        $aHeader = array();
        foreach ($oConfig->fields->children() as $vKey => $oValue) {
            if ($oValue->header) {
                $aHeader[$vKey] = (string) $oValue->header;
            } else {
                $aHeader[$vKey] = $vKey;
            }
        }

        foreach ($this->_oWriters as $oWriter) {
            $oWriter->setHeader($aHeader)->writeHeaderRow();
        }

        return $this;
    }


    /**
     * Closes the file writers once finished
     *
     * @return $this
     */
    protected function _closeWriters() {
        foreach ($this->_oWriters as $oWriter) {
            $oWriter->close();
        }
        return $this;
    }
}
