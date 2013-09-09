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
        $this->setHeader(array( // TODO Does not work
                'identifier' => 'id',
                'name' => 'title',
                'description' => 'description',
                'gshopping_category' => 'google product category',
                'category' => 'product type',
                'url' => 'link',
                'image' => 'image link',
                'gshopping_condition' => 'condition',
                'availability' => 'availability',
                'price' => 'Price',
                'special_price' => 'sale price',
                'special_price_date' => 'sale price effective date',
                'brand' => 'brand',
                'sku' => 'mpn',
                'identifier_exists' => 'identifier_exists',
            ))->writeHeaderRow();

        // Initialise the formatter
        Mage::getSingleton('aligent_feeds/log')->log("Initialising Google Shopping Formatter...");
        Mage::getSingleton('aligent_feeds/googleshopping_formatter')->init($oStore);
        Mage::getSingleton('aligent_feeds/log')->log("Initialised Google Shopping Formatter.");
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
                $aArgs['this']->log("Exporting product #".$aArgs['idx']."  SKU: ".$aArgs['row']['sku'], Zend_Log::DEBUG, true);
                if (($aArgs['idx'] % 100) == 0) {
                    $aArgs['this']->log("Exporting product #".$aArgs['idx']."...", Zend_Log::INFO);
                    $aArgs['this']->logMemoryUsage();
                }

                $aExportableRow = Mage::getSingleton('aligent_feeds/googleshopping_formatter')->prepareRow($aArgs['row']);
                if ($aExportableRow !== false) {
                    $aArgs['writer']->writeDataRow($aExportableRow);
                }
            }), array(
                'writer' => $oFileWriter,
                'this' => $this
            ));

        $oIo->streamClose();
        Mage::getSingleton('aligent_feeds/log')->log("Finished Google Shopping data export for store #".$oStore->getId()." - ".$oStore->getName());
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
        return $this;
    }


    protected function _initWriters(Mage_Core_Model_Store $oStore, $vFeedname, Mage_Core_Model_Config_Element $oConfig) {
        foreach ($oConfig->output->getChildren() as $oOutputFile) {
            switch (trim($oOutputFile->format)) {
                case 'csv':
                    $this->_oWriters = Mage::getModel('aligent_feeds/writer_csv')->init($oStore->getCode(), $vFeedname, $oOutputFile);
                    break;
                case 'xml':
                    $this->_oWriters = Mage::getModel('aligent_feeds/writer_xml')->init($oStore->getCode(), $vFeedname, $oOutputFile);
                    break;
            }
        }
        return $this;
    }


}
