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

    const CONFIG_FTP_ENABLED = 'feeds/general/ftp';
    const CONFIG_FTP_HOST = 'feeds/general/ftp_host';
    const CONFIG_FTP_USER = 'feeds/general/ftp_user';
    const CONFIG_FTP_PASS = 'feeds/general/ftp_pass';
    const CONFIG_FTP_PATH = 'feeds/general/ftp_path';


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
        Mage::getSingleton('aligent_feeds/feed_formatter')->init($oStore, $oConfig);
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

        // Allow the feed definition to include a "before_query_filter".  This method
        // will be allowed to modify the query before it's executed.
        if ($oConfig->before_query_filter) {
            Mage::getSingleton('aligent_feeds/log')->log("Calling before query filter...");
            $vClass = (string) $oConfig->before_query_filter->class;
            $vMethod = (string) $oConfig->before_query_filter->method;
            $aParams = (array) $oConfig->before_query_filter->params;

            Mage::getSingleton($vClass)->{$vMethod}($oSelect, $oStore, $aParams);
            Mage::getSingleton('aligent_feeds/log')->log("Before query filter done.");
        }

        Mage::getSingleton('aligent_feeds/log')->log("Exporting products...");
        $oResource = Mage::getModel('core/resource_iterator')->walk($oSelect, array(
            function($aArgs) {
                Mage::getSingleton('aligent_feeds/log')->log("Exporting product #".$aArgs['idx']."  SKU: ".$aArgs['row']['sku'], Zend_Log::DEBUG, true);
                if (($aArgs['idx'] % 100) == 0) {
                    Mage::getSingleton('aligent_feeds/log')->log("Exporting product #".$aArgs['idx']."...", Zend_Log::INFO);
                    Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
                }

                $aRows = Mage::getSingleton('aligent_feeds/feed_formatter')->prepareRow($aArgs['row']);

                if (count($aRows) > 0) {
                    foreach ($aRows as $aRow) {
                        foreach ($aArgs['writers'] as $oWriter) {
                            $oWriter->writeDataRow($aRow);
                        }
                    }
                }
            }), array(
                'writers' => $this->_oWriters,
                'config' => $oConfig,
                'store' => $oStore,
            ));

        $this->_closeWriters();
        $this->_sendFeed();

        Mage::getSingleton('aligent_feeds/status')->addSuccess("Generated $vFeedname data for store #".$oStore->getId()." - ".$oStore->getName());
        Mage::getSingleton('aligent_feeds/log')->log("Finished $vFeedname data export for store #".$oStore->getId()." - ".$oStore->getName());
        Mage::getSingleton('aligent_feeds/log')->logMemoryUsage();
        return $this;
    }


    protected function _initWriters(Mage_Core_Model_Store $oStore, $vFeedname, Mage_Core_Model_Config_Element $oConfig) {
        foreach ($oConfig->output->children() as $oOutputFile) {
            switch (trim($oOutputFile->format)) {
                case 'csv':
                    $oWriter = Mage::getModel('aligent_feeds/writer_csv')->init($oStore->getCode(), $vFeedname, $oOutputFile, $oConfig->fields);
                    if ($oWriter instanceof Aligent_Feeds_Model_Writer_Abstract) {
                        $this->_oWriters[] = $oWriter;
                    }
                    break;
                case 'xml':
                    $oWriter = Mage::getModel('aligent_feeds/writer_xml')->init($oStore->getCode(), $vFeedname, $oOutputFile, $oConfig->fields);
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
            // If the field's definition includes an "<exclude />" tag, remove it from the export data.
            foreach ($oValue->children() as $vType => $data) {
                if ($vType == 'exclude') {
                    continue(2);
                }
            }


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


    /**
     * Upload Feed to FTP server if required.
     */
    protected function _sendFeed() {
        if (Mage::getStoreConfigFlag(self::CONFIG_FTP_ENABLED)) {
            $oFtp = new Varien_Io_Ftp();
            $bSuccess = $oFtp->open(
                array(
                    'host' => Mage::getStoreConfig(self::CONFIG_FTP_HOST),
                    'user' => Mage::getStoreConfig(self::CONFIG_FTP_USER),
                    'password' => Mage::getStoreConfig(self::CONFIG_FTP_PASS),
                )
            );
            if (!$bSuccess) {
                Mage::getSingleton('aligent_feeds/log')->log("Unable to connect to FTP Server");
                Mage::getSingleton('aligent_feeds/status')->addError("", "Unable to connect to FTP Server");
                return;
            }

            $bSuccess = $oFtp->cd(Mage::getStoreConfig(self::CONFIG_FTP_PATH));
            if (!$bSuccess) {
                Mage::getSingleton('aligent_feeds/log')->log("Unable change directories on FTP Server");
                Mage::getSingleton('aligent_feeds/status')->addError("", "Unable to connect to FTP Server");
                return;
            }

            foreach ($this->_oWriters as $oWriter) {
                $vFilename = $oWriter->getFilename();
                $bSuccess = $oFtp->write(basename($vFilename), $vFilename);
                if (!$bSuccess) {
                    Mage::getSingleton('aligent_feeds/log')->log("Unable to upload $vFilename to FTP server");
                    Mage::getSingleton('aligent_feeds/status')->addError("", "Unable to upload $vFilename to FTP server");
                    return;
                }
            }

            $oFtp->close();
        }
    }
}
