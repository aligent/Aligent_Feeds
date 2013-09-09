<?php

class Aligent_Feeds_Model_Googleshopping {


    public function export(Mage_Core_Model_Store $oStore) {
        $this->log("Beginning Google Shopping data export for store #".$oStore->getId()." - ".$oStore->getName());
        $this->logMemoryUsage();

        // Get the feed file name.  Abort if the file is not writable.
        $vFileName = $this->_getFeedFileName($oStore);
        if ($vFileName === false) {
            $this->log('Feed export aborted.');
            return $this;
        }
        $this->log("Using filename: ".$vFileName);

        // Open the output file
        $oIo = new Varien_Io_File();
        $oIo->open(array('path' => dirname($vFileName)));
        $oIo->streamOpen($vFileName);

        // Prepare the csv file header
        $oFileWriter = Mage::getModel('aligent_feeds/simplefilewriter')
            ->setStreamWriter($oIo)
            ->setHeader(array(
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
        $this->log("Initialising Google Shopping Formatter...");
        Mage::getSingleton('aligent_feeds/googleshopping_formatter')->init($oStore);
        $this->log("Initialised Google Shopping Formatter.");
        $this->logMemoryUsage();

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
            )->where('visibility IN (?)', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
            ->where('horizon_import_status = ?', Modelflight_Import_Model_Horizon_Status::STATUS_COMPLETE);

        $this->log("Exporting products...");
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
        $this->log("Finished Google Shopping data export for store #".$oStore->getId()." - ".$oStore->getName());
        $this->logMemoryUsage();
        return $this;
    }


    /**
     * Logging for Feed exporter
     * @param string $message
     * @param int $level  ZEND_LOG log level
     * @param boolean $bDeveloperModeOnly True to log only in Developer mode
     */
    public function log($message, $level = Zend_Log::INFO, $bDeveloperModeOnly = false) {
        if ($bDeveloperModeOnly == false || ($bDeveloperModeOnly == true && Mage::getIsDeveloperMode())) {
            Mage::log($message, $level, Modelflight_Feeds_Model_Cron::LOG_FILE);
        }
    }

    public function logMemoryUsage() {
        $iCurrentKb = ceil(memory_get_usage(true) / 1024);
        $iPeakKb = ceil(memory_get_peak_usage(true) / 1024);
        $this->log("Memory Usage - Current (Kb): ".$iCurrentKb."   Peak (Kb): ".$iPeakKb, Zend_Log::DEBUG);
    }

    protected function _getFeedFileName(Mage_Core_Model_Store $oStore) {
        $vFeedDir = Mage::getBaseDir().'/feeds';
        if (!is_dir($vFeedDir)) {
            $this->_log("Feed export directory does not exist: ".$vFeedDir);
            return false;
        }
        $vFileName = $vFeedDir.'/googleshopping-'.$oStore->getCode().'.csv';
        if (!file_exists($vFileName)) {
            touch($vFileName);
        }
        if (is_writable($vFileName)) {
            return $vFileName;
        } else {
            $this->_log("Feed file is not writable: ".$vFileName);
            return false;
        }
    }

}
